<?php 

namespace Swage\Cli\Command;

use Aws\Credentials\Credentials;
use Aws\Lambda\LambdaClient;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use Bref\Lambda\{
    InvocationFailed,
    InvocationResult,
    SimpleLambdaClient
};

class Console extends Command
{
    const SUCCESS = 0;
    const ERROR = 1;

    /**
     * Command name
     * 
     * @var string
     */
    protected static $defaultName = 'cli';

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    public function __construct(string $cwd)
    {
        parent::__construct();

        $this->cwd = $cwd;
        $this->config = $this->getConfig();
    }

    protected function configure()
    {
        $this
            ->setDescription("Console")
            ->setHelp("Execute commants for function.")
            ->addArgument('function', InputArgument::REQUIRED, 'Name of your function.')
            ->addArgument('arguments', InputArgument::IS_ARRAY, 'Remote arguments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'swage-cli', 'Version 1.0', 'Console', ' '
        ]);

        $awsDefaults = $this->getAwsDefaults();
        $awsDefaults['version'] = 'latest';
        $functionName = $input->getArgument('function');
    
        // Because arguments may contain spaces, and are going to be executed remotely
        // as a separate process, we need to escape all arguments.
        $arguments = array_map(static function (string $arg): string {
            return escapeshellarg($arg);
        }, $input->getArgument('arguments'));

        var_dump($arguments);

        try {

            $event = json_encode([
                'cli' => implode(' ', $arguments),
            ]);

            // var_dump($event); exit;

            $result = (new LambdaClient($awsDefaults))->invoke([
                'FunctionName' => $functionName,
                'LogType' => 'Tail',
                'Payload' => $event ?? '',
            ]);

            $payload = json_decode($result['Payload'], true);
            var_dump($payload);

            if ($result['FunctionError']) { // Presence indicates error
                throw new \Exception($result['FunctionError']);
            }

        } catch (\Exception $e) {
            if (isset($payload['errorType'])) $output->writeln('<error>'.$payload['errorType'].'</error>');
            if (isset($payload['errorMessage']))  $output->writeln('<comment>' . $payload['errorMessage'] . '</comment>');
            if (isset($payload['stackTrace'])) {
                $output->writeln('<info>Stack trace:</info>');
                foreach($payload['stackTrace'] as $st) {
                    $output->writeln($st);
                }
            }
            return static::ERROR;
        }

        $output->writeln($payload['output']);

        return (int) ($payload['exitCode'] ?? 1);
        
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

    private function getConfig(): Collection
    {
        $config = Yaml::parseFile($this->cwd.'/swage-cli/swage.yaml');
        return new Collection($config);
    }



}
