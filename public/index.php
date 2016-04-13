<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// if (PHP_SAPI == 'cli-server') {
//     // To help the built-in PHP dev server, check if the request was actually for
//     // something which should probably be served as a static file
//     $file = __DIR__ . $_SERVER['REQUEST_URI'];
//     if (is_file($file)) {
//         return false;
//     }
// }

require APPLICATION_PATH . '/../../vendor/autoload.php';

$settings = require APPLICATION_PATH . '/config/global.php';

// Eloquent
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['settings']['eloquent']);
$capsule->setEventDispatcher( new \Illuminate\Events\Dispatcher( new \Illuminate\Container\Container ));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Setup app
$app = new Slim\App($settings);
require APPLICATION_PATH . '/dependencies.php';
require APPLICATION_PATH . '/middleware.php';
require APPLICATION_PATH . '/routes.php';

// Run app
$app->run();
