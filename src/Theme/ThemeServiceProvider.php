<?php

namespace Creatyon\Core\Theme;

use App;
use Creatyon\Core\Theme\Middleware\RouteMiddleware;
use Creatyon\Core\Theme\Middleware\WebMiddleware;
use File;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!File::exists(public_path('themes')) && config('theme.symlink') && File::exists(config('theme.theme_path'))) {
            App::make('files')->link(config('theme.theme_path'), public_path('themes'));
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
        $this->registerTheme();
        AliasLoader::getInstance()->alias('Theme', ThemeFacade::class);
        $this->registerMiddleware();

    }

    /**
     * Add Theme Types Middleware.
     *
     * @return void
     */
    public function registerMiddleware()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('theme', RouteMiddleware::class);
        $router->pushMiddlewareToGroup('web', WebMiddleware::class);

        if (config('theme.types.enable')) {
            $themeTypes = config('theme.types.middleware');
            foreach ($themeTypes as $middleware => $themeName) {
                $this->app['router']->aliasMiddleware($middleware, '\Creatyon\Core\Theme\Middleware\RouteMiddleware:'.$themeName);
            }
        }
    }

    /**
     * Register theme required components .
     *
     * @return void
     */
    public function registerTheme()
    {
        $this->app->singleton(ThemeContract::class, function ($app) {
            $theme = new ThemeManager($app, $this->app['view']->getFinder(), $this->app['translator']);

            return $theme;
        });
    }

    /**
     * Publish config file.
     *
     * @return void
     */
    public function publishConfig()
    {
        $configPath = realpath(__DIR__.'../../Common/config/theme.php');
        $this->mergeConfigFrom($configPath, 'theme');
    }

}
