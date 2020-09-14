<?php

namespace Swage\Cli\Aws\CloudFormation;

use Aws\CloudFormation\CloudFormationClient;
use Aws\Credentials\Credentials;

class CreateStack 
{
    public const VERSION = 'latest'; // 2010-05-08

    protected $client;

    protected $stackName;

    protected $templateBody;

    protected $clientRequestToken;

    /**
     * The Amazon Resource Name (ARN) of an AWS Identity and Access Management (IAM) 
     * role that AWS CloudFormation assumes to create the stack. AWS CloudFormation 
     * uses the role's credentials to make calls on your behalf. AWS CloudFormation 
     * always uses this role for all future operations on the stack. As long as users 
     * have permission to operate on the stack, AWS CloudFormation uses this role even 
     * if the users don't have permission to pass it. 
     * Ensure that the role grants least privilege.
     * 
     * @var string
     */
    protected $roleARN;

    protected $resourceTypes;

    public static function create(array $config) 
    {

        return new CreateStack($config);

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

    public function setTemplateBody(string $template): void 
    {
        $this->templateBody = $template;
    }

    public function setClientRequestToken(string $token): void
    {
        $this->clientRequestToken = $token;
    }

    public function setResourceTypes(array $types): void 
    {
        $this->resourceTypes = $types;
    }

    public function setRoleARN(string $arn): void 
    {
        $this->roleARN = $arn;
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

        return $this->client->createStack($payload);
    
    }
}
