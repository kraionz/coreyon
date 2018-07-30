<?php

namespace Creatyon\Core\Theme\Middleware;

use Closure;
use Creatyon\Core\Theme\Theme as ThemeModel;

class WebMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $setting = function_exists('setting')? setting('detect_admin_middleware') : false;

        if ( $setting & request()->is( env('BACKEND_PATH')?? 'admin'.'/*') || $setting & request()->is( env('BACKEND_PATH').'/*')){

            $this->setThemeAdmin();

        }else {

            $this->setTheme();
        }

        return $next($request);
    }

    public function setTheme(){

        $active = check_table_db(config('theme.database.table'))? ThemeModel::where([['admin', false], ['active', true]])->first() : null;

        $theme = $active ? $active->theme : config('theme.default.site');
        if(\Theme::has($theme)){
            \Theme::set($theme);
        }
    }

    public function setThemeAdmin(){

        $active = check_table_db(config('theme.database.table'))? ThemeModel::where([['admin', true], ['active', true]])->first() : null;
        $theme = $active ? $active->theme : config('theme.default.admin');

        if(\Theme::has($theme)){
            \Theme::set($theme);
        }
    }
}
