<?php

namespace Mhasnainjafri\RestApiKit\Commands;

use Illuminate\Console\Command;

class SetupAuthCommand extends Command
{
    protected $signature = 'RestApiKit:setup-auth';

    protected $description = 'Set up authentication for RestApiKit';

    public function handle()
    {

        // Ask if Sanctum or Passport should be used
        $authOption = $this->choice('Which authentication method do you want to use?', ['Sanctum', 'Passport'], 0);

        // Update the config based on choice
        if ($authOption === 'Passport') {
            $this->info('Updating configuration for Passport...');
            $this->updateConfig('auth:passport');
        } else {
            $this->info('Updating configuration for Sanctum...');
            $this->updateConfig('auth:sanctum');
        }

        $this->info('Auth setup completed!');
    }

    private function updateConfig($authMiddleware)
    {
        $configPath = config_path('RestApiKit.php');
        $config = file_get_contents($configPath);
        $updatedConfig = str_replace("'auth:sanctum'", "'$authMiddleware'", $config);
        file_put_contents($configPath, $updatedConfig);
    }
}
