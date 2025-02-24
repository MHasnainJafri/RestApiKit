<?php

namespace Mhasnainjafri\RestApiKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'rest:clear-cache {key? : The cache key to clear}';

    protected $description = 'Clear cache for a specific key or all keys.';

    public function handle()
    {
        $key = $this->argument('key');

        if ($key) {
            Cache::forget($key);
            $this->info("Cache for key '{$key}' has been cleared.");
        } else {
            Cache::flush();
            $this->info('All cache has been cleared.');
        }
    }
}
