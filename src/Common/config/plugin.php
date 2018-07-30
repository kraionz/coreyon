<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugin config name and change log file name
    |--------------------------------------------------------------------------
    |
    | Here is the config for plugin.json file and changelog
    | for version control status
    |
    */

    'config'     => [
        'name'      => 'plugin.json',
        'changelog' => 'changelog.json',
    ],


    /*
    |--------------------------------------------------------------------------
    | Database Config
    |--------------------------------------------------------------------------
    |
    | Here you can specify plugin database settings
    |
    */
    'database' => [
        'table' => 'plugins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin path
    |--------------------------------------------------------------------------
    |
    | This path used for save the generated plugin. This path also will added
    | automatically to list of scanned folders.
    |
    */
    'path' => app_path('Plugins'),

];
