<?php

namespace Swage\Cli\Aws\Iam;

use Aws\Iam\IamClient;
use Aws\Credentials\Credentials;

class GetUser 
{
    public const VERSION = 'latest'; // 2010-05-08

    protected $client;

    public static function create(array $config): GetUser 
    {

        return new GetUser($config);

    }

    public function __construct(array $args) 
    {
        $args['version'] = static::VERSION;

        $this->client = new IamClient($args);
    }

    public function handle(): \Aws\Result
    {

        return $this->client->getUser([]);
    
    }
}