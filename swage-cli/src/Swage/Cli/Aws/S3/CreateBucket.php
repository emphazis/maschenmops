<?php 

namespace Swage\Cli\Aws\S3;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

class CreateBucket 
{
    const VERSION = 'latest'; // 2006-03-01

    protected $client;

    protected $bucketName;

    public static function create(array $config): CreateRole 
    {
        return new CreateRole($config);
    }

    public function __construct($config)
    {
        $config['version'] = CreateBucket::VERSION;

        $this->client = new \Aws\S3\S3Client($config);
    }

    public function setBucketName($bucketName): void
    {
        $this->bucketName = $bucketName;
    }

    public function handle() 
    {
        return $result = $this->client->createBucket([
            'Bucket' => $this->bucketName
        ]);
    }

}
