<?php 

namespace Swage\Cli\Aws\S3;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

use  \GuzzleHttp\Psr7;

class PutObject 
{
    const VERSION = 'latest'; // 2006-03-01

    protected $client;

    protected $bucketName;

    protected $resource;

    protected $key;

    public static function create(array $config): PutObject 
    {
        return new PutObject($config);
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

    public function setResource(string $file): void
    {
        $this->resource = Psr7\stream_for(fopen($file, 'r'));
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function handle() 
    {
        return $result = $this->client->putObject([
            'Body' => $this->resource,
            'Key' => $this->key,
            'Bucket' => $this->bucketName
        ]);
    }

}
