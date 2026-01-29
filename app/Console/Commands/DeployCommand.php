<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

final class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the application with automated updates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting deployment process...');

        try {
            // Enable maintenance mode
            $this->info('🔧 Enabling maintenance mode...');
            $this->call('down');

            // Validate deploy script exists
            $deployScriptPath = base_path('deploy.sh');
            if (!file_exists($deployScriptPath)) {
                throw new Exception('Deploy script not found at: ' . $deployScriptPath);
            }

            // Make script executable (Unix-like systems)
            if (PHP_OS_FAMILY !== 'Windows') {
                $this->info('🔑 Making deploy script executable...');
                Process::run('chmod +x ' . $deployScriptPath);
            }

            // Execute deployment script with 6-minute timeout
            $this->info('📦 Executing deployment script...');
            $this->line('');

            $result = Process::timeout(60 * 6)
                ->run($this->getDeployCommand($deployScriptPath));

            // Check if script failed
            if ($result->failed()) {
                throw new Exception('Deployment script failed: ' . $result->errorOutput());
            }

            // Show output
            if ($result->output()) {
                $this->line($result->output());
            }

            // Disable maintenance mode
            $this->info('🌐 Bringing application back online...');
            $this->call('up');

            // Success messages
            $this->line('');
            $this->info('✅ Deployment completed successfully!');

            Log::info('Deployment completed successfully via artisan command');

            return Command::SUCCESS;

        } catch (Exception $e) {
            // Handle errors
            $this->error('❌ Deployment failed: ' . $e->getMessage());

            // Attempt recovery
            $this->warn('⚠️ Attempting to bring application back online...');
            $this->call('up');

            Log::error('Deployment failed via artisan command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Get the deploy command based on the operating system
     */
    private function getDeployCommand(string $scriptPath): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // On Windows, use sh or bash if available
            return "sh \"{$scriptPath}\"";
        }

        // On Unix-like systems, use sudo if configured in the documentation
        // For development, just run the script directly
        return "sh \"{$scriptPath}\"";
    }
}
