<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

final class ManagementController extends Controller
{
    /**
     * Deploy the application
     */
    public function deploy(Request $request): JsonResponse
    {
        $error = $this->validateManagementKey($request);
        if ($error !== null) {
            return $error;
        }

        try {
            Log::info('Deployment started via management API');

            Artisan::call('app:deploy', ['--force' => true]);
            $output = Artisan::output();

            Log::info('Deployment completed successfully', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Deployment completed successfully',
                'output' => $output,
            ]);
        } catch (Exception $e) {
            Log::error('Deployment failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Deployment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop the application (enable maintenance mode)
     */
    public function stop(Request $request): JsonResponse
    {
        $error = $this->validateManagementKey($request);
        if ($error !== null) {
            return $error;
        }

        try {
            Log::info('Stopping application via management API');

            Artisan::call('down');
            $output = Artisan::output();

            Log::info('Application stopped successfully', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Application stopped successfully',
                'output' => $output,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to stop application', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to stop application: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start the application (disable maintenance mode)
     */
    public function start(Request $request): JsonResponse
    {
        $error = $this->validateManagementKey($request);
        if ($error !== null) {
            return $error;
        }

        try {
            Log::info('Starting application via management API');

            Artisan::call('up');
            $output = Artisan::output();

            Log::info('Application started successfully', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Application started successfully',
                'output' => $output,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to start application', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start application: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute a custom artisan script
     */
    public function customScript(Request $request): JsonResponse
    {
        $error = $this->validateManagementKey($request);
        if ($error !== null) {
            return $error;
        }

        $validated = $request->validate([
            'command' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $command = $validated['command'];
        $description = $validated['description'] ?? 'No description provided';

        // Security check: Only allow artisan commands
        if (!str_starts_with($command, 'php artisan ')) {
            return response()->json([
                'success' => false,
                'message' => 'Only artisan commands are allowed',
            ], 400);
        }

        try {
            Log::info('Executing custom script via management API', [
                'command' => $command,
                'description' => $description,
            ]);

            // Extract the artisan command (remove "php artisan " prefix)
            $artisanCommand = substr($command, 12);

            // Parse command and arguments
            $parts = explode(' ', $artisanCommand);
            $commandName = array_shift($parts);
            $arguments = [];

            foreach ($parts as $part) {
                if (str_starts_with($part, '--')) {
                    $arguments[$part] = true;
                } else {
                    $arguments[] = $part;
                }
            }

            Artisan::call($commandName, $arguments);
            $output = Artisan::output();

            Log::info('Custom script executed successfully', [
                'command' => $command,
                'output' => $output,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Custom script executed successfully',
                'command' => $command,
                'output' => $output,
            ]);
        } catch (Exception $e) {
            Log::error('Custom script execution failed', [
                'command' => $request->input('command'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Script execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get application status
     */
    public function status(Request $request): JsonResponse
    {
        $error = $this->validateManagementKey($request);
        if ($error !== null) {
            return $error;
        }

        $isMaintenanceMode = app()->isDownForMaintenance();

        return response()->json([
            'success' => true,
            'app_id' => config('app.id'),
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'external_url' => config('app.external_url'),
            'is_maintenance' => $isMaintenanceMode,
            'status' => $isMaintenanceMode ? 'maintenance' : 'running',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Validate the management secret key
     */
    private function validateManagementKey(Request $request): ?JsonResponse
    {
        // Accept key from query parameter OR header
        $secretKey = $request->query('key') ?? $request->header('X-Management-Key');

        // Compare with configured secret
        if ($secretKey !== config('app.management_secret_key')) {
            Log::warning('Unauthorized management API access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        return null; // Valid key
    }
}
