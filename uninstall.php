<?php

/**
 * External Storage Module - Uninstallation Script
 *
 * Runs when the module is uninstalled via TadreebLMS External Apps system.
 *
 * This script:
 *   1. Removes the "manage external storage" Spatie permission
 *   2. Removes the "external-s3" disk from config/filesystems.php
 *   3. Removes copied helper files from the main app
 */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

// ---------------------------------------------------------------------------
// 1. Remove Spatie permission
// ---------------------------------------------------------------------------
try {
    $permission = Permission::where('name', 'manage external storage')
        ->where('guard_name', 'web')
        ->first();

    if ($permission) {
        // Detach from all roles first
        $permission->roles()->detach();
        $permission->delete();
    }

    // Clear cached permissions
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    Log::info('[ExternalStorage] Permission "manage external storage" removed.');
} catch (\Exception $e) {
    Log::error('[ExternalStorage] Failed to remove permissions: ' . $e->getMessage());
}

// ---------------------------------------------------------------------------
// 2. Remove "external-s3" disk from config/filesystems.php
// ---------------------------------------------------------------------------
try {
    $filesystemsPath = base_path('config/filesystems.php');
    $contents = File::get($filesystemsPath);

    // Remove the external-s3 block (between our markers)
    $pattern = '/\n\s*\/\/ --- External Storage Module \(external-s3 disk\) ---.*?\/\/ --- End External Storage Module ---/s';
    $newContents = preg_replace($pattern, '', $contents);

    if ($newContents !== $contents) {
        File::put($filesystemsPath, $newContents);
        Log::info('[ExternalStorage] Removed external-s3 disk from config/filesystems.php.');
    }
} catch (\Exception $e) {
    Log::error('[ExternalStorage] Failed to remove external-s3 from filesystems.php: ' . $e->getMessage());
}

// ---------------------------------------------------------------------------
// 3. Remove copied helper files
// ---------------------------------------------------------------------------
try {
    $helpersDir = app_path('Helpers/ExternalStorage');

    if (File::isDirectory($helpersDir)) {
        File::deleteDirectory($helpersDir);
        Log::info('[ExternalStorage] Removed app/Helpers/ExternalStorage/ directory.');
    }
} catch (\Exception $e) {
    Log::error('[ExternalStorage] Failed to remove helper files: ' . $e->getMessage());
}

echo "External Storage module uninstalled successfully!\n";
