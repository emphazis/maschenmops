<?php

namespace Swage\Cli\Aws\Iam;

use Aws\Iam\IamClient;
use Aws\Credentials\Credentials;

class CreateRole 
{
    public const VERSION = 'latest'; // 2010-05-08

    protected $client;

    protected $roleName;

    protected $policyDocument;

    public static function create(array $config): CreateRole 
    {
        return new CreateRole($config);
    }

    public function __construct(array $args)
    {
        $args['version'] = static::VERSION;

        $this->client = new IamClient($args);
    }

    public function setRoleName($roleName): void 
    {
        $this->roleName = $roleName;
    }

    public function setPolicyDocument(string $path): void 
    {
        $this->policyDocument = file_get_contents($path);
    }

    public function handle(): \Aws\Result
    {
        return $this->client->createRole([
            'AssumeRolePolicyDocument' => $this->policyDocument, // REQUIRED
            'Description' => 'created by swage.de',
            'Path' => '/service-role/',
            'RoleName' => $this->roleName, // REQUIRED
        ]);
    }
}