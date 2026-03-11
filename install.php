<?php

/**
 * External Storage Module - Installation Script
 *
 * Runs when the module is installed via TadreebLMS External Apps system.
 *
 * This script:
 *   1. Creates the "manage external storage" Spatie permission and assigns to Administrator
 *   2. Patches config/filesystems.php to add the "external-s3" disk
 */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$modulePath = dirname(__FILE__);

// ---------------------------------------------------------------------------
// 1. Seed Spatie permissions
// ---------------------------------------------------------------------------
try {
    $permissionName = 'manage external storage';
    $permission = Permission::firstOrCreate(
        ['name' => $permissionName, 'guard_name' => 'web']
    );

    $adminRole = Role::where('name', 'Administrator')->first();
    if ($adminRole && !$adminRole->hasPermissionTo($permissionName)) {
        $adminRole->givePermissionTo($permission);
    }

    // Clear cached permissions so the new one takes effect
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    Log::info('[ExternalStorage] Permission "manage external storage" created and assigned to Administrator.');
} catch (\Exception $e) {
    Log::error('[ExternalStorage] Failed to seed permissions: ' . $e->getMessage());
}

// ---------------------------------------------------------------------------
// 2. Patch config/filesystems.php to add the "external-s3" disk
// ---------------------------------------------------------------------------
try {
    $filesystemsPath = base_path('config/filesystems.php');
    $contents = File::get($filesystemsPath);

    // Only patch if external-s3 disk doesn't already exist
    if (strpos($contents, "'external-s3'") === false) {

        // Safe approach: find the closing "]," of the disks array (the line with just
        // whitespace + "]," before the final "];") and insert our block before it.
        // We search for the pattern:  \n    ],\n\n]; at the end of the file.
        $diskEntry = "\n" .
            "        // --- External Storage Module (external-s3 disk) ---\n" .
            "        'external-s3' => [\n" .
            "            'driver' => 's3',\n" .
            "            'key' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ACCESS_KEY_ID'),\n" .
            "            'secret' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_SECRET_ACCESS_KEY'),\n" .
            "            'region' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_DEFAULT_REGION') ?: 'us-east-1',\n" .
            "            'bucket' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_BUCKET'),\n" .
            "            'url' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_URL') ?: null,\n" .
            "            'endpoint' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT') ?: null,\n" .
            "            'root' => \\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ROOT') ?: null,\n" .
            "            'use_path_style_endpoint' => !empty(\\App\\Services\\ExternalApps\\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT')),\n" .
            "            'visibility' => 'private',\n" .
            "        ],\n" .
            "        // --- End External Storage Module ---\n";

        // Step 1: Ensure the last array entry before "],\n];" has a trailing comma.
        // Find every "]" NOT followed by "," that appears before the disks array close.
        // Pattern: "]" followed by whitespace/newline then "]," or just "];"
        $contents = preg_replace('/\](\s*\n\s*\],\s*\n\s*\];)/', '],${1}', $contents);

        // Step 2: Insert our disk block right before the disks array closing "],\n];"
        $closingPattern = '/(\n\s*\],\s*\n\];)\s*$/';
        if (preg_match($closingPattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPos = $matches[1][1];
            $contents = substr($contents, 0, $insertPos) . $diskEntry . substr($contents, $insertPos);
        }

        // Step 3: Validate syntax BEFORE writing — never write broken PHP
        $syntaxCheck = trim(preg_replace('/^<\?php\s*/', '', $contents));
        $valid = @eval('return true; ' . $syntaxCheck);
        if ($valid !== true) {
            throw new \Exception('Filesystem config patch would produce invalid PHP — aborting to protect the app.');
        }

        File::put($filesystemsPath, $contents);
        Log::info('[ExternalStorage] Patched config/filesystems.php with external-s3 disk.');
    } else {
        Log::info('[ExternalStorage] external-s3 disk already exists in filesystems.php, skipping patch.');
    }
} catch (\Exception $e) {
    Log::error('[ExternalStorage] Failed to patch filesystems.php: ' . $e->getMessage());
}

echo "External Storage module installed successfully!\n";
