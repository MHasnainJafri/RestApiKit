<?php

namespace Mhasnainjafri\RestApiKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class MakeRepository extends Command
{
    protected $signature = 'make:repository 
                            {model : The name of the model}
                            {--resource= : The resource class name}
                            {--searchable= : Comma-separated searchable fields}
                            {--sortable= : Comma-separated sortable fields}
                            {--relations= : Comma-separated default relations}
                            {--cache-tag= : Cache tag name}
                            {--per-page=15 : Default items per page}';

    protected $description = 'Create a new repository class for a model';

    public function handle()
    {
        if (!$this->option('resource')) {
            $resource = $this->ask('Enter resource class name (leave empty for JsonResource)');
            $this->input->setOption('resource', $resource);
        }
        
        $model = $this->argument('model');
        $modelClass = "App\\Models\\{$model}";
        
        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist!");
            return;
        }

        $stub = $this->getStub();
        $replacements = $this->getReplacements($model);
        
        $directory = app_path('Repositories');
        if (!File::exists($directory)) {
            File::makeDirectory($directory);
        }

        $filePath = "{$directory}/{$model}Repository.php";
        
        if (File::exists($filePath)) {
            $this->error("Repository already exists!");
            return;
        }

        File::put($filePath, str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        ));

        $this->info("Repository created successfully: {$model}Repository.php");
    }

    protected function getStub()
    {
        $this->info(__DIR__);
        return File::get(__DIR__.'/../../stubs/repository.stub');
    }

    protected function getReplacements($model)
    {
        $modelClass = "App\\Models\\{$model}";
        $fillableFields = $this->getFillableFields($modelClass);

        return [
            '{{model}}' => $model,
            '{{resource}}' => $this->option('resource') ?: 'JsonResource',
            '{{resourceNamespace}}'=>$this->option('resource')?'App\\Http\\Resources\\':'Illuminate\\Http\\Resources\\Json\\',
            '{{searchable}}' => $this->formatArrayOption('searchable'),
            '{{sortable}}' => $this->formatArrayOption('sortable'),
            '{{relations}}' => $this->formatArrayOption('relations'),
            '{{cacheTag}}' => $this->option('cache-tag') ?: 'null',
            '{{perPage}}' => $this->option('per-page'),
            '{{storeValidationRules}}' => $this->generateValidationRules($fillableFields, 'store'),
            '{{updateValidationRules}}' => $this->generateValidationRules($fillableFields, 'update'),
        ];
    }

    protected function formatArrayOption($option)
    {
        $value = $this->option($option);
        return $value ? "['".str_replace(',', "', '", $value)."']" : '[]';
    }

    protected function getFillableFields($modelClass)
    {
        $model = new $modelClass;
        return $model->getFillable();
    }


protected function generateValidationRules(array $fillableFields, string $operation): string
{
    // Fetch the model class name from the argument
    $modelName = $this->argument('model');
    $modelClass = "App\\Models\\{$modelName}";

    // Get the $casts property of the model
    $casts = [];
    if (class_exists($modelClass)) {
        $modelInstance = new $modelClass();
        $casts = $modelInstance->getCasts();
    }

    $rules = [];
    foreach ($fillableFields as $field) {
        // Skip timestamps
        if ($field === 'created_at' || $field === 'updated_at' || $field==='id') {
            continue;
        }

        // Start with a base rule
        $rule = 'required';

        // Add field-specific rules based on casts or other conditions
        if (isset($casts[$field])) {
            switch ($casts[$field]) {
                case 'integer':
                    $rule .= '|integer';
                    break;
                case 'float':
                case 'double':
                case 'real':
                    $rule .= '|numeric';
                    break;
                case 'boolean':
                    $rule .= '|boolean';
                    break;
                case 'date':
                case 'datetime':
                    $rule .= '|date';
                    break;
                case 'array':
                case 'json':
                    $rule .= '|array';
                    break;
                default:
                    $rule .= '|string|max:255';
                    break;
            }
        } else {
            // General rules for fields without casts
            if (Str::endsWith($field, '_id')) {
                $rule .= '|exists:' . Str::replaceLast('_id', '', $field) . ',id';
            } elseif (Str::contains($field, 'email')) {
                $rule .= '|email|unique:' . Str::plural(Str::lower($modelName));
            } elseif (Str::contains($field, 'password')) {
                $rule .= '|min:8';
            } elseif (Str::contains($field, 'phone')) {
                $rule .= '|numeric';
            } elseif (Str::contains($field, 'date')) {
                $rule .= '|date';
            } else {
                $rule .= '|string|max:255';
            }
        }

        // For update operation, make fields optional
        if ($operation === 'update') {
            $rule = 'sometimes|' . $rule;
        }

        $rules[] = "'{$field}' => '{$rule}'";
    }

    // Return the rules as a formatted array
    return "[\n            " . implode(",\n            ", $rules) . "\n        ]";
}
}