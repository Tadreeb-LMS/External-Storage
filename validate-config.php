<?php

/**
 * External Storage Module - Configuration Validation Script
 *
 * Runs when admin saves configuration via the TadreebLMS configure form.
 *
 * Available in scope:
 *   $app           - ExternalApp model instance
 *   $configuration - array of key-value pairs from the POST form data
 *
 * To signal validation failure: throw an \Exception with the error message.
 */

$driver = $configuration['STORAGE_DRIVER'] ?? '';

// Validate storage driver value
if (!in_array($driver, ['local', 's3'])) {
    throw new \Exception('Storage Driver must be either "local" or "s3".');
}

// If S3 driver selected, require essential credentials
if ($driver === 's3') {
    $required = [
        'S3_ACCESS_KEY_ID' => 'S3 Access Key ID',
        'S3_SECRET_ACCESS_KEY' => 'S3 Secret Access Key',
        'S3_DEFAULT_REGION' => 'S3 Region',
        'S3_BUCKET' => 'S3 Bucket Name',
    ];

    $missing = [];
    foreach ($required as $key => $label) {
        if (empty($configuration[$key])) {
            $missing[] = $label;
        }
    }

    if (!empty($missing)) {
        throw new \Exception('The following fields are required when using S3 driver: ' . implode(', ', $missing));
    }
}
