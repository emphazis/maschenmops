<?php

namespace Swage\Cli\Aws\Iam;

use Aws\Iam\IamClient;
use Aws\Credentials\Credentials;

class GetRole 
{
    public const VERSION = 'latest'; // 2010-05-08

    protected $client;

    protected $roleName;

    public static function create(array $config): GetRole 
    {
        return new GetRole($config);
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

    public function handle(): \Aws\Result
    {
        return $this->client->getRole([
            'RoleName' => $this->roleName, // REQUIRED
        ]);

    }
}