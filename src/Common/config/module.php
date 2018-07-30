<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module config name and change log file name
    |--------------------------------------------------------------------------
    |
    | Here is the config for module.json file and changelog
    | for version control status
    |
    */

    'config'     => [
        'name'      => 'module.json',
        'changelog' => 'changelog.json',
    ],


    /*
    |--------------------------------------------------------------------------
    | Database Config
    |--------------------------------------------------------------------------
    |
    | Here you can specify modules database settings
    |
    */
    'database' => [
        'table' => 'modules',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules path
    |--------------------------------------------------------------------------
    |
    | This path used for save the generated module. This path also will added
    | automatically to list of scanned folders.
    |
    */
    'path' => app_path('Modules'),

];
