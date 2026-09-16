<?php

// Lazily loaded only when S3 usage is actually attempted (see includes/s3_storage.php),
// so local dev with no AWS setup and no `composer install` never touches this file.
function get_s3_client(): Aws\S3\S3Client
{
    static $client = null;

    if ($client === null) {
        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => getenv('AWS_REGION') ?: 'us-east-1',
        ]);
    }

    return $client;
}
