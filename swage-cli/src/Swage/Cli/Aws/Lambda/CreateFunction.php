<?php

namespace Swage\Cli\Aws\Lambda;

use Aws\Lambda\LambdaClient;
use Aws\Credentials\Credentials;

class CreateFunction 
{
    public const VERSION = 'latest'; // "2015-03-31"

    /**
     * @var \Aws\Lambda\LambdaClient
     */
    private $client;

    /**
     * The name of the Lambda function (example-function)
     * 
     * @var string
     */
    protected $functionName;

    /**
     * 
     * @var string
     */
    protected $s3BucketName;

    /**
     * 
     * @var string
     */
    protected $s3Key;

    /**
     * The name of the method within your code that Lambda calls to execute your function. 
     * The format includes the file name. It can also include namespaces and other qualifiers, depending on the runtime.
     *
     * @var string
     */
    protected $handler;

    /**
     * A list of function layers to add to the function's execution environment. 
     * Specify each layer by its ARN, including the version.
     * 
     * @var array
     */
    protected $layers;

    /**
     * The Amazon Resource Name (ARN) of the function's execution role.
     * 
     * @var string
     */
    protected $role;

    /**
     * Set to true to publish the first version of the function during creation.
     * 
     * @var boolean
     */
    protected $publish = true;

    /**
     * 
     * @var \Swage\Cli\Aws\Environment
     */
    protected $environment;

    /**
     * 
     * @var string
     */
    protected $runtime = 'provided';


    /**
     * @var int
     */
    protected $functionTimeout = 3;


    public static function create(array $args): PublishLayerVersion 
    {
        return new PublishLayerVersion($args);
    }

    public function __construct(array $args)
    {
        $args['version'] = static::VERSION;

        $this->client = new LambdaClient($args);
    }

    public function setFunctionName(string $functionName): void 
    {
        $this->functionName = $functionName;
    }

    public function setS3BucketName(string $bucketName): void 
    {
        $this->s3BucketName = $bucketName;
    }

    public function setS3Key(string $key): void 
    {
        $this->s3Key = $key;
    }

    public function setHandler(string $handler): void
    {
        $this->handler = $handler;
    }

    public function setRole(string $arn): void 
    {
        $this->role = $arn;
    }

    public function setRuntime(string $runtime): void 
    {
        $this->runtime = $runtime;
    }

    public function setEnvironment(array $variables): void 
    {
        $this->environment = \Swage\Cli\Aws\Environment::fromArray($variables);
    }

    public function setLayers(array $layers): void 
    {
        $this->layers = $layers;
    }

    public function setFunctionTimeout(int $seconds): void 
    {
        $this->functionTimeout = $seconds;
    }


    public function handle(): \Aws\Result
    {
        return $this->client->createFunction([
            'Code' => [ // REQUIRED
                'S3Bucket' => $this->s3BucketName,
                'S3Key' => $this->s3Key,
            ],
            // 'Environment' => [
            //     'Variables' => $this->environment->toArray(),
            // ],
            'FunctionName' => $this->functionName, // REQUIRED
            'Handler' => $this->handler, // REQUIRED
            'Layers' => $this->layers,
            'Publish' => $this->publish,
            'Role' => $this->role, // REQUIRED
            'Runtime' => $this->runtime, // REQUIRED
            'Timeout' => $this->functionTimeout
        ]);
    }
}