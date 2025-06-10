<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nameInput = $this->argument('name');

        // Check if there's a directory structure in the name
        if (Str::contains($nameInput, '/')) {
            $className = class_basename($nameInput);
            $relativePath = Str::beforeLast($nameInput, '/');
            $fullPath = "{$relativePath}/{$className}";
            $namespace = 'App\\Services\\' . str_replace('/', '\\', $relativePath);
        } else {
            // Simple case - just a service name with no directory
            $className = $nameInput;
            $fullPath = $className;
            $namespace = 'App\\Services';
        }

        $path = app_path("Services/{$fullPath}.php");

        if (File::exists($path)) {
            $this->error("Service {$className} already exists!");

            return;
        }

        $stub = File::get(base_path('stubs/service.stub'));

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $stub
        );

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✅ Service created: {$namespace}\\{$className}");
    }
}
