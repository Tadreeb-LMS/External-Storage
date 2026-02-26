<?php
namespace Modules\ExternalStorage\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Services\ExternalApps\ExternalAppService;

class ExternalStorageController extends Controller
{
    /**
     * Show storage settings form.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $app = \App\Models\ExternalApp::where('slug', 'external-storage')->first();

        if (!$app || !$app->is_enabled) {
            return response()->json(['error' => 'Module not available'], 403);
        }

        $settings = [
            'STORAGE_DRIVER'      => ExternalAppService::staticGetModuleEnv('external-storage', 'STORAGE_DRIVER') ?: 'local',
            'S3_ACCESS_KEY_ID'    => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ACCESS_KEY_ID') ?: '',
            'S3_SECRET_ACCESS_KEY'=> ExternalAppService::staticGetModuleEnv('external-storage', 'S3_SECRET_ACCESS_KEY') ?: '',
            'S3_DEFAULT_REGION'   => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_DEFAULT_REGION') ?: 'us-east-1',
            'S3_BUCKET'           => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_BUCKET') ?: '',
            'S3_URL'              => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_URL') ?: '',
            'S3_ENDPOINT'         => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT') ?: '',
            'S3_ROOT'             => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ROOT') ?: '',
        ];

        // Load view directly from module's views directory
        $viewPath = base_path('modules/external-storage/views/settings.blade.php');
        return view()->file($viewPath, compact('settings'));
    }

    /**
     * Save storage settings to module .env file.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'storage_driver'      => 'required|in:local,s3',
            's3_access_key_id'    => 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_secret_access_key'=> 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_default_region'   => 'required_if:storage_driver,s3|nullable|string|max:50',
            's3_bucket'           => 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_url'              => 'nullable|string|max:500',
            's3_endpoint'         => 'nullable|string|max:500',
            's3_root'             => 'nullable|string|max:255',
        ]);

        $service = app(ExternalAppService::class);
        $service->setModuleEnv('external-storage', [
            'STORAGE_DRIVER'      => $request->input('storage_driver', 'local'),
            'S3_ACCESS_KEY_ID'    => $request->input('s3_access_key_id', ''),
            'S3_SECRET_ACCESS_KEY'=> $request->input('s3_secret_access_key', ''),
            'S3_DEFAULT_REGION'   => $request->input('s3_default_region', 'us-east-1'),
            'S3_BUCKET'           => $request->input('s3_bucket', ''),
            'S3_URL'              => $request->input('s3_url', ''),
            'S3_ENDPOINT'         => $request->input('s3_endpoint', ''),
            'S3_ROOT'             => $request->input('s3_root', ''),
        ]);

        return redirect('/external-apps/external-storage/settings')
            ->with('success', 'Storage settings have been saved successfully.');
    }

    /**
     * Test S3 connection via AJAX.
     */
    public function testConnection(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $key      = $request->input('s3_access_key_id');
        $secret   = $request->input('s3_secret_access_key');
        $region   = $request->input('s3_default_region', 'us-east-1');
        $bucket   = $request->input('s3_bucket');
        $endpoint = $request->input('s3_endpoint');

        if (empty($key) || empty($secret) || empty($bucket)) {
            return response()->json([
                'success' => false,
                'message' => 'Please fill in Access Key, Secret Key, and Bucket Name before testing.',
            ], 400);
        }

        $tester = new \Modules\ExternalStorage\Services\S3ConnectionTester();
        $result = $tester->test([
            'key'      => $key,
            'secret'   => $secret,
            'region'   => $region,
            'bucket'   => $bucket,
            'endpoint' => $endpoint,
        ]);

        return response()->json($result);
    }
}
?>
