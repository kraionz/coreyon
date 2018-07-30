<?php

namespace Creatyon\Core\Plugin;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;


class PluginServiceProvider extends ServiceProvider
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

        $plugins = PluginFacade::all();
        foreach ($plugins as $plugin) {
            $this->path = config('plugin.path').'/'.$plugin['name'];
            if($plugin->get('core') || $plugin->get('active'))
                $this->app->register($plugin->get('provider'));
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
        $this->registerPlugin();
        AliasLoader::getInstance()->alias('Plugin', PluginFacade::class);

    }

    /**
     * Register Pluging.
     *
     * @return void
     */
    public function registerPlugin()
    {
        $this->app->singleton(PluginContract::class, function () {
            $plugin = new PluginManager();
            return $plugin;
        });
    }

    /**
     * Publish config file.
     *
     * @return void
     */
    public function publishConfig()
    {
        $configPath = realpath(__DIR__.'../../Common/config/plugin.php');
        $this->mergeConfigFrom($configPath, 'plugin');
    }


}
