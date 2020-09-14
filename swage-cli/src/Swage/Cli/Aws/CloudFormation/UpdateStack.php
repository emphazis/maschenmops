<?php

namespace Swage\Cli\Aws\CloudFormation;

use Aws\CloudFormation\CloudFormationClient;
use Aws\Credentials\Credentials;

class UpdateStack extends CreateStack 
{
    public static function create(array $config)
    {
        return new UpdateStack($config);
    } 

    public function __construct(array $config) 
    {
        parent::__construct($config);
    }

    public function handle(): \Aws\Result
    {
        $payload = [
            'Capabilities' => ['CAPABILITY_IAM', 'CAPABILITY_AUTO_EXPAND'],
            'StackName' => $this->stackName,
            'ClientRequestToken' => $this->clientRequestToken,
            // 'ResourceTypes' => $this->resourceTypes,
            // 'RoleARN' => $this->roleARN,
            'TemplateBody' => $this->templateBody,

        ];

        return $this->client->updateStack($payload);
    
    }
}
