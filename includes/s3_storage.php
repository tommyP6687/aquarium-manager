<?php

// Uploads a rendered sprite PNG to S3 and returns its public URL, or null on any
// failure (SDK not installed, no bucket configured, missing credentials, network
// error, etc.). Callers must treat null as "skip -- keep using the grid fallback",
// never as a fatal error: saving a sprite must always succeed even with no AWS
// setup at all, which is the default for local development.
function uploadSpriteToS3(int $userId, int $spriteId, string $pngBytes): ?string
{
    $bucket = getenv('S3_BUCKET');
    if (!$bucket) {
        return null;
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoload)) {
        error_log('S3 upload skipped: run "composer install" to enable it (vendor/autoload.php not found).');
        return null;
    }
    require_once $autoload;
    require_once __DIR__ . '/../config/aws.php';

    $key = "sprites/{$userId}/{$spriteId}.png";

    try {
        $result = get_s3_client()->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => $pngBytes,
            'ContentType' => 'image/png',
        ]);

        return $result['ObjectURL'] ?? null;
    } catch (\Throwable $e) {
        error_log('S3 sprite upload failed: ' . $e->getMessage());
        return null;
    }
}
