<?php namespace Creatyon\Core\Commands;

use File;
use Illuminate\Console\Command;
use League\Flysystem\Filesystem;
use Illuminate\Database\Eloquent\Model;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all Creatyon package seeders.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $paths = File::files(__DIR__.'/../Common/database/seeds');

        foreach ($paths as $path) {

            Model::unguarded(function () use ($path) {
                $namespace = 'Creatyon\Core\Common\database\seeds\\'.basename($path, '.php');
                $this->getSeeder($namespace)->__invoke();
            });
        }

        $this->info('Seeded database successfully.');
    }

    /**
     * Get a seeder instance from the container.
     *
     * @param string $namespace
     * @return \Illuminate\Database\Seeder
     */
    protected function getSeeder($namespace)
    {
        $class = $this->laravel->make($namespace);

        return $class->setContainer($this->laravel)->setCommand($this);
    }
}
