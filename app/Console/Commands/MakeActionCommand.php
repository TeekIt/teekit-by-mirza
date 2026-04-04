<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeActionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name}';

    protected $successCode = Command::SUCCESS;

    protected $failureCode = Command::FAILURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action class';

    /**
     * Command specific code
     */
    protected $namespace = 'App\\Actions';

    protected $className;

    public function getClassStub(): string
    {
        return <<<EOT
                <?php

                namespace {$this->namespace};

                final class {$this->className}
                {
                    public function __construct() {}

                    public function execute() {}
                }
                EOT;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $name = $this->argument('name');

            /* Handle nested directories */
            if (str_contains($name, '/')) {
                $parts = explode('/', $name);
                $this->className = array_pop($parts);
                $this->namespace .= '\\' . implode('\\', $parts);
                $path = app_path('Actions/' . implode('/', $parts));
            } else {
                $this->className = $name;
                $path = app_path('Actions');
            }

            File::ensureDirectoryExists($path, 0755);

            $filePath = $path . '/' . $this->className . '.php';

            /* Get the relative path from the project root */
            $relativePath = str_replace(base_path() . '/', '', $filePath);

            /* Check if file already exists */
            if (File::exists($filePath)) {
                $this->error("Action class [{$relativePath}] already exists!");

                return $this->failureCode;
            }

            File::put($filePath, $this->getClassStub());

            $this->info("Action class [{$relativePath}] created successfully.");

            return $this->successCode;
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return $this->failureCode;
        }
    }
}
