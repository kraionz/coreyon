<?php

namespace Creatyon\Core\Traits;

use Illuminate\Support\Facades\Route;

trait RoutesTrait{

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAdminRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    public function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group($this->dir.'/../Common/routes/web.php');
    }

    /**
     * Define the "Admin" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */

    public function mapAdminRoutes()
    {
        Route::prefix(env('BACKEND_PATH') ?? 'admin')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group($this->dir.'/../Common/routes/admin.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    public function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group($this->dir.'/../Common/routes/api.php');
    }

}
