<?php

// default settings
$settings = [
    'settings' => [

        'eloquent' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => APPLICATION_PATH . '/../data/logs/app.log',
        ],
    ],
];

// load environment settings
if (file_exists(APPLICATION_PATH . '/config/' . APPLICATION_ENV . '.php')) {
    $settings = array_replace_recursive(
        $settings,
        require APPLICATION_PATH . '/config/' . APPLICATION_ENV . '.php'
    );
}

return $settings;
