<?php

namespace Modules\ExternalStorage\Services;

use Aws\S3\S3Client;
use Exception;

class S3ConnectionTester
{
    /**
     * Test S3 connection using headBucket API call.
     *
     * @param  array  $credentials  Keys: key, secret, region, bucket, endpoint
     * @return array{success: bool, message: string}
     */
    public function test(array $credentials): array
    {
        if (empty($credentials['key']) || empty($credentials['secret']) || empty($credentials['bucket'])) {
            return [
                'success' => false,
                'message' => 'Please fill in all required S3 fields before testing.',
            ];
        }

        try {
            $config = [
                'version' => 'latest',
                'region'  => $credentials['region'] ?: 'us-east-1',
                'credentials' => [
                    'key'    => $credentials['key'],
                    'secret' => $credentials['secret'],
                ],
            ];

            if (!empty($credentials['endpoint'])) {
                $config['endpoint'] = $credentials['endpoint'];
                $config['use_path_style_endpoint'] = true;
            }

            $client = new S3Client($config);
            $client->headBucket(['Bucket' => $credentials['bucket']]);

            return [
                'success' => true,
                'message' => 'Connection successful! Your S3 bucket is accessible.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
