<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action class';

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
            $namespace = 'App\\Actions\\' . str_replace('/', '\\', $relativePath);
        } else {
            // Simple case - just a action name with no directory
            $className = $nameInput;
            $fullPath = $className;
            $namespace = 'App\\Actions';
        }

        $path = app_path("Actions/{$fullPath}.php");

        if (File::exists($path)) {
            $this->error("Action {$className} already exists!");

            return;
        }

        $stub = File::get(base_path('stubs/action.stub'));

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $stub
        );

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✅ Action created: {$namespace}\\{$className}");
    }
}
