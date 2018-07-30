<?php

namespace Creatyon\Core\Module;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Creatyon\Core\Support\Helper;
use Illuminate\Support\ServiceProvider;


class ModuleServiceProvider extends ServiceProvider
{
    protected $files;
    protected $path;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->files = new Filesystem;

        $modules = ModuleFacade::all();

        foreach ($modules as $module) {
            $this->path = config('module.path').'/'.$module['name'];
            if($module->get('core') || $module->get('active'))
                $this->app->register($module->get('provider'));
                $this->loads($module);
                $this->publish();
        }

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->publishConfig();
        $this->registerModule();
        AliasLoader::getInstance()->alias('Module', ModuleFacade::class);

    }

    /**
     * Load files Module.
     *
     * @param $module
     * @return void
     */
    public function loads($module)
    {
        $name = $module->get('str');

        $config = $this->path.'/Common/config/'.$name.'.php';
        $helper = $this->path.'/Common/helpers';
        $views  = $this->path.'/Common/resources/views';
        $trans  = $this->path.'/Common/resources/lang';
        $migrations  = $this->path.'/Common/database/migrations';

          //if($module->get('str') == 'setting'){
          //  dd($trans, $this->files->isDirectory($trans));
          //}

        if ($this->files->exists($config)) {
            $this->mergeConfigFrom($config, $name);
        }
        if ($this->files->isDirectory($helper)) {
            Helper::autoload($helper);
        }
        if ($this->files->isDirectory($views)) {
            $this->loadViewsFrom($views, str_plural($name));
        }
        if ($this->files->isDirectory($trans)) {
            $this->loadTranslationsFrom($trans, str_plural($name));
        }
        if ($this->files->isDirectory($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

    }

    /**
     * Publish files.
     *
     * @return void
     */
    public function publish()
    {
        $migrations  = $this->path .'/database/migrations';

        if (app()->runningInConsole()) {
            $this->publishes([$migrations => database_path('migrations')], 'migrations');
        }
    }

    /**
     * Register Modules.
     *
     * @return void
     */

    public function registerModule()
    {
        $this->app->singleton(ModuleContract::class, function () {
            $module = new ModuleManager();
            return $module;
        });
    }

    /**
     * Publish config file.
     *
     * @return void
     */
    public function publishConfig()
    {
        $configPath = realpath(__DIR__.'../../Common/config/module.php');
        $this->mergeConfigFrom($configPath, 'module');
    }


}
