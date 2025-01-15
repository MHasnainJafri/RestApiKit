<?php

namespace Mhasnainjafri\RestApiKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreatePolicyCommand extends Command
{
    protected $signature = 'restify:policy {name} {--model=}';

    protected $description = 'Create a new policy with RestApiKit scaffold';

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?? Str::studly(Str::before($name, 'Policy'));

        $stubPath = __DIR__.'/../../stubs/policy.stub';
        $stub = file_get_contents($stubPath);

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}'],
            ['App\\Policies', $name, $model],
            $stub
        );

        $path = base_path("app/Policies/{$name}.php");

        if (file_exists($path)) {
            $this->error("Policy {$name} already exists!");

            return;
        }

        file_put_contents($path, $content);
        $this->info("Policy {$name} created successfully!");
    }
}
