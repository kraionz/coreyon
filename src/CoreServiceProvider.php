<?php
namespace Creatyon\Core;

use Creatyon\Core\Commands\SeedCommand;
use Creatyon\Core\Module\ModuleServiceProvider;
use Creatyon\Core\Plugin\PluginServiceProvider;
use Creatyon\Core\Support\Helper;
use Creatyon\Core\Theme\ThemeServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends  ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
        $this->commonFiles();
        $this->app->register(ModuleServiceProvider::class);
        $this->app->register(ThemeServiceProvider::class);
        $this->app->register(PluginServiceProvider::class);
    }

    public function commonFiles()
    {
        $this->loadRoutesFrom(__DIR__ . '/Common/routes/web.php');
        Helper::autoload(realpath(__DIR__.'/Common/helpers'));
        $this->loadMigrationsFrom(__DIR__ . '/Common/database/migrations');
        $configPath = realpath(__DIR__.'/Common/config/core.php');
        $this->mergeConfigFrom($configPath, 'core');
    }

    private function registerCommands()
    {
        $this->commands([
            SeedCommand::class,
        ]);
    }

}


