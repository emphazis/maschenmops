<?php 

namespace Swage\Cli\Command;

use Aws\Credentials\Credentials;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use Swage\Cli\Aws\S3\{CreateBucket, HeadBucket, PutObject};
use Swage\Cli\Aws\Lambda\{CreateFunction, PublishLayerVersion};
use Swage\Cli\Aws\Iam;
use Swage\Cli\Aws\CloudFormation;
use Swage\Cli\Config\Parameters;
use Swage\Cli\Helper\{CustomFilterIterator, Zip};

class DeployCommand extends Command
{
    const SUCCESS = 0;
    const ERROR = 1;
    const ARTIFACTS_FOLDER = 'swage-artifacts';

    /**
     * Command name
     * 
     * @var string
     */
    protected static $defaultName = 'deploy';

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Filesystem\Finder
     */
    protected $finder;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    public function __construct(string $cwd)
    {
        parent::__construct();

        $this->cwd = $cwd;
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
        $this->config = $this->getConfig();
    }

    protected function configure()
    {
        $this
            ->setDescription("Deploy")
            ->setHelp("This command helps you to deploy.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'swage-cli', 'Version 1.0', ' ', ' '
        ]);

        $requestToken = Parameters::CLOUD_FORMATION_CLIENT_REQUEST_TOEKN . "-" . \time();
        $pathToArtifacts = '/home/vagrant';

        $awsDefaults = $this->getAwsDefaults();
        $roleNameDefault = Parameters::IAM_SERVICE_ROLE;

        $handler = Iam\GetRole::create($awsDefaults);
        $handler->setRoleName($roleNameDefault);

        try {
            $response = $handler->handle();
            $roleArn = $response['Role']['Arn'];
            $output->write("Using IAM role with ARN: $roleArn", true);
        } catch (\Aws\Exception\AwsException $e) {
            $output->write($e->getAwsErrorMessage().PHP_EOL);
        }

        if (! isset($roleArn)) { // Create new IAM role

            $handler = Iam\CreateRole::create($awsDefaults);
            $handler->setRoleName($roleNameDefault);
            $handler->setPolicyDocument($this->cwd . "/swage-cli/cloud-formation-service-role.json");

            try {
                $response = $handler->handle();
                $roleArn = $response['Role']['Arn'];
                $output->write("Created IAM role with ARN $roleArn.", true);
            } catch (\Aws\Exception\AwsException $e) {
                $output->write($e->getAwsErrorMessage().PHP_EOL);
                return static::ERROR;
            }

        }

        /**
         * AWS S3
         * Upload files
         */
        $bucketName = "swage-files";
        $bucket = null;

        $headBucketHandler = new HeadBucket($awsDefaults);
        $headBucketHandler->setBucketName($bucketName);
        try {
            $bucket = $headBucketHandler->handle();
            $output->write("Found Bucket \"$bucketName\".", true);
        } catch (\Aws\Exception\AwsException $e) {
            $output->write($e->getAwsErrorMessage().PHP_EOL);
        }

        if (! $bucket) { // Create bucket if not exists

            $crateBucketHandler = new CreateBucket($awsDefaults);
            $crateBucketHandler->setBucketName($bucketName);
    
            try {
                $bucket = $crateBucketHandler->handle();
                $output->write("Created Bucket \"$bucketName\".", true);
            } catch (\Aws\Exception\AwsException $e) {
                $output->write($e->getAwsErrorMessage().PHP_EOL);
            }
        }

        // PubObjects into bucket
        $finder = new Finder();
        $finder->files()->name(['*.zip']);
        $finder->depth('== 0');
        $finder->in(realpath($pathToArtifacts . "/swage-artifacts/storefront"));

        $putObjectHandler = new PutObject($awsDefaults);

        $uploadedFiles = [];
        foreach ($finder as $file) {
            $putObjectHandler->setBucketName($bucketName);
            $putObjectHandler->setResource($file->getRealpath());
            $putObjectHandler->setKey($file->getFilename());

            $putObjectHandler->handle();
        }

        /**
         * LambdaClient - PublishLayerVersion - vendor
         */
        $publishLayerVersion = new PublishLayerVersion($awsDefaults);
        $publishLayerVersion->setRuntimes(['provided']);
        $publishLayerVersion->setBucketName($bucketName);
        $publishLayerVersion->setS3Key("vendor-layer.zip");
        $publishLayerVersion->setLayerName("swage-vendor-layer");
        $vendorLayer = $publishLayerVersion->handle();

        $publishLayerVersion = new PublishLayerVersion($awsDefaults);
        $publishLayerVersion->setRuntimes(['provided']);
        $publishLayerVersion->setBucketName($bucketName);
        $publishLayerVersion->setS3Key("src-layer.zip");
        $publishLayerVersion->setLayerName("swage-src-layer");
        $srcLayer = $publishLayerVersion->handle();

        /**
         * Definte template.yaml
         */
        $template = $this->getTemplate(
            $this->cwd . "/swage-cli/template.yaml", 
            [
                '__TASK_LAYER__' => 's3://swage-files/task-layer.zip',
                '__VENDOR_LAYER__' => $vendorLayer['LayerVersionArn'], // arn:aws:lambda:eu-central-1:596504905365:layer:swage-src-layer:1
                '__SRC_LAYER__' => $srcLayer['LayerVersionArn'], // arn:aws:lambda:eu-central-1:596504905365:layer:swage-vendor-layer:1
            ]
        );

        /**
         * CloudFormationClient::GetTemplate - CreateStack from template.yaml
         */
        $cfGetTemplate = CloudFormation\GetTemplate::create($awsDefaults);
        $cfGetTemplate->setStackName(Parameters::CLOUD_FORMATION_STACK_NAME);

        try {
            $result = $cfGetTemplate->handle();
            $updateStack = true;
        }
        catch (\Aws\Exception\AwsException $e) {
            $output->write($e->getAwsErrorMessage().PHP_EOL);
        }

        
        if (! isset($updateStack)) {
            $cloudFormation = CloudFormation\CreateStack::create($awsDefaults);
        }
        else {
            $cloudFormation = CloudFormation\UpdateStack::create($awsDefaults);
        }

        $cloudFormation->setStackName(Parameters::CLOUD_FORMATION_STACK_NAME);
        $cloudFormation->setRoleARN($roleArn);
        $cloudFormation->setClientRequestToken($requestToken);
        $cloudFormation->setResourceTypes([
            'AWS::*'
        ]);
        $cloudFormation->setTemplateBody($template);

        try {
            $createStackResponse = $cloudFormation->handle();
        }
        catch (\Aws\Exception\AwsException $e) {
            $output->write($e->getAwsErrorMessage().PHP_EOL);
            return static::ERROR;
        }

        var_dump($createStackResponse);
        exit;

        $createFunctionHandler = new CreateFunction($awsDefaults);
        $createFunctionHandler->setFunctionName('swage-storefront');
        $createFunctionHandler->setS3BucketName($bucketName);
        $createFunctionHandler->setS3Key('src-layer.zip');
        $createFunctionHandler->setHandler('src/public/index.php');
        $createFunctionHandler->setRuntime('provided');
        $createFunctionHandler->setEnvironment($_ENV);
        $createFunctionHandler->setRole($roleArn);
        $createFunctionHandler->setLayers([
            'arn:aws:lambda:eu-central-1:209497400698:layer:php-73:25',
            'arn:aws:lambda:eu-central-1:209497400698:layer:php-73-fpm:25',
            $runtimeLayer['LayerVersionArn'],
            $vendorLayer['LayerVersionArn'],
        ]);

        $response = $createFunctionHandler->handle();

        var_dump($response);
        
        return static::ERROR;

        $handler = Iam\GetUser::create($awsDefaults);

        $response = $handler->handle();
        $output->writeln((string) $response);

        return Command::SUCCESS;
        
    }

    private function getAwsDefaults(): array 
    {
        return [
            'region' => $this->config->get('aws')['region'],
            'credentials' => new Credentials(
                                $this->config->get('aws')['key'],
                                $this->config->get('aws')['secret']
                            )
        ];
    }

    private function getTemplate(string $path, array $replace = []): string 
    {
        $template = file_get_contents($path);

        if (empty($replace)) return $template;

        return str_replace(array_keys($replace), array_values($replace), $template);

    }

    private function getConfig(): Collection
    {
        $config = Yaml::parseFile($this->cwd.'/swage-cli/swage.yaml');
        return new Collection($config);
    }

    private function runProcess(Process $process): void
    {
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo $buffer;
            }
        });
    }


}
