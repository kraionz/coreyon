<?php

if (!function_exists('is_active_route')) {
    /**
     * Check if Active Route.
     *
     * @param $route
     * @param string $output
     * @return string
     */
    function is_active_route($route, $output = "active")
    {
        $currentRouteName =  Route::currentRouteName();

        // Convert to Array
        if (!is_array($route))
        {
            $routePattern = explode(' ', $route);
        }
        // Check the current route name
        foreach ((array) $route as $i)
        {
            if (str_is($i, $currentRouteName))
            {
                return $output;
            }
        }

    }
}

if (!function_exists('check_table_db')) {
    /**
     * Check if there a table in database.
     *
     * @return boolean
     */
    function check_table_db($table)
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable($table);

        } catch (\Exception $e) {

            return false;
        }


    }
}

if (!function_exists('scan_folder')) {
    /**
     * @param $path
     * @param array $ignore_files
     * @return array
     */
    function scan_folder($path, $ignore_files = [])
    {
        try {
            if (is_dir($path)) {
                $data = array_diff(scandir($path), array_merge(['.', '..'], $ignore_files));
                natsort($data);
                return $data;
            }
            return [];
        } catch (Exception $ex) {
            return [];
        }
    }
}
