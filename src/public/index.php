<?php
// die("Hellooooooooo!!!!!");
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Config\ConfigFactory;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
require '../vendor/autoload.php';

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
date_default_timezone_set('Asia/Kolkata');
// Register an autoloader
$loader = new Loader();


$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);
$loader->registerNamespaces([
  
    'App\Listeners' => APP_PATH . '/listeners'
]);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set( 
    "cookies", function () { 
       $cookies = new Cookies();  
       $cookies->useEncryption(false);  
       return $cookies; 
    } 
 ); 

$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );

        $session
            ->setAdapter($files)
            ->start();

        return $session;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$eventManager = new EventsManager();
$eventManager->attach(
    'spotify',
    new App\Listeners\notificationListeners()
);

$container->set(
    'eventManager',
    $eventManager
);

$application = new Application($container);

$container->set(
    'config',
    function () {
        $fileName = '../app/etc/config.php';
        $factory = new ConfigFactory();
        return $factory->newInstance("php",$fileName);
        // return new Config($configData);
    }
);


$container->set(
    'db',
    function () {
        return new Mysql(
            [
    
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'spotify',
                ]
            );
        }
);

$container->set(
    'mongo',
    function () {
        $mongo = new MongoClient();

        return $mongo->selectDB('db');
    },
    true
);


try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
