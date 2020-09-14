<?php 

namespace Swage\Cli\Aws\S3;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

class HeadBucket 
{
    const VERSION = 'latest'; // 2006-03-01

    protected $client;

    protected $bucketName;

    public static function create(array $config): HeadBucket 
    {
        return new HeadBucket($config);
    }

    public function __construct(array $config)
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
        return $result = $this->client->headBucket([
            'Bucket' => $this->bucketName
        ]);
    }

}
