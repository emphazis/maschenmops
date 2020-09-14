<?php

namespace Swage\Cli\Aws\Lambda;

use Aws\Lambda\LambdaClient;
use Aws\Credentials\Credentials;

class PublishLayerVersion 
{
    public const VERSION = 'latest'; // 2015-03-31"

    protected $client;

    protected $runtimes = ['provided'];

    protected $layerName;

    protected $s3BucketName;

    protected $s3Key;

    public static function create(array $args): PublishLayerVersion 
    {
        return new PublishLayerVersion($args);
    }

    public function __construct(array $args)
    {
        $args['version'] = static::VERSION;

        $this->client = new LambdaClient($args);
    }

    public function setRuntimes(array $runtimes): void 
    {
        $this->runtimes = $runtimes;
    }

    public function setBucketName(string $bucketName): void 
    {
        $this->s3BucketName = $bucketName;
    }

    public function setS3Key(string $key): void 
    {
        $this->s3Key = $key;
    }

    public function setLayerName(string $layerName): void 
    {
        $this->layerName = $layerName;
    }

    public function handle(): \Aws\Result
    {
        return $this->client->publishLayerVersion([
            'CompatibleRuntimes' => $this->runtimes,
            'Content' => [ // REQUIRED
                'S3Bucket' => $this->s3BucketName,
                'S3Key' => $this->s3Key
            ],
            'LayerName' => $this->layerName, // REQUIRED
            'LicenseInfo' => 'none',
        ]);
    }
}