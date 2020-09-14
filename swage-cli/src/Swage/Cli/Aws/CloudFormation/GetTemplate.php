<?php

namespace Swage\Cli\Aws\CloudFormation;

use Aws\CloudFormation\CloudFormationClient;
use Aws\Credentials\Credentials;

class GetTemplate 
{
    public const VERSION = 'latest'; // 2010-05-08

    protected $client;

    /**
     * @var string
     */
    protected $stackName;

    public static function create(array $config): GetTemplate 
    {

        return new GetTemplate($config);

    }

    public function __construct(array $args)
    {
        $args['version'] = static::VERSION;

        $this->client = new CloudFormationClient($args);
    }

    public function setStackName($stackName): void 
    {
        $this->stackName = $stackName;
    }

    public function handle(): \Aws\Result
    {
        $payload = [
            'StackName' => $this->stackName,
            'TemplateStage' => 'Original',
        ];

        return $this->client->getTemplate($payload);
    
    }
}
