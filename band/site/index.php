<?php
require 'vendor/autoload.php';
session_start();

//set the timezone
date_default_timezone_set('UTC');

defined('APPLICATION_ROOT') || define('APPLICATION_ROOT', realpath(dirname(__FILE__) ));
defined('COOKIEPATH') || define('COOKIEPATH', '/');
defined('RECAPTCHA_PUBLIC_KEY') || define('RECAPTCHA_PUBLIC_KEY', '6LcwIw4TAAAAAHZUTiZMeANIn8t642EcXKR01jNw');
defined('RECAPTCHA_PRIVATE_KEY') || define('RECAPTCHA_PRIVATE_KEY', '6LcwIw4TAAAAABAm5WNFsqEe04VSnLIUvXwpoxIL');

//TWIG
$loader = new Twig_Loader_Filesystem(APPLICATION_ROOT . '/views');
$twig = new Twig_Environment($loader, array(
    'debug' => true
    // Twig options go here.
));

//SLIM
$app = new \Slim\Slim(array(
    'cookies.encrypt'       => true,
    'cookies.secret_key'    => 'kc',
    'cookies.cipher'        => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode'   => MCRYPT_MODE_CBC,
    'debug'                 => true,
    'view'                  => new \Slim\Views\Twig(),
    'templates.path'        => APPLICATION_ROOT . '/views'
));

$view = $app->view();
$view->parserOptions = array(
    'debug'         => true,
    //'cache' => dirname(__FILE__) . '/../cache'
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new Twig_Extension_Debug()
);


/* DATABASE */
$config = array(
    // required credentials
    'host'       => '45.55.180.122',
    'user'       => 'karolcowan',
    'password'   => 'karolcowan',
    'database'   => 'karol_cowan',

    // optional
    'fetchMode'  => \PDO::FETCH_ASSOC,
    'charset'    => 'utf8',
    'port'       => 3306,
    'unixSocket' => null,
);

// standard setup
$dbConn = new \Simplon\Mysql\Mysql(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database']
);
$sqlManager = new \Simplon\Mysql\Manager\SqlManager($dbConn);


$app->error(function (\Exception $e) use ($app) {
    echo $app->view->render('404.html', array(
        'message' => $e->getMessage()
    ));
});

$app->notFound(function (\Exception $e) use ($app) {
    echo $app->view->render('404.html', array(
        'message' => $e->getMessage()
    ));
});


$app->hook('slim.before', function() use ($app) {
    $baseUrl = getBaseURI();

    $data['base_url'] = $baseUrl;
    $app->view()->appendData($data);
});

$app->get('/', function() use ($app) {
    echo $app->view->render('home.twig', array(
        'tab' => 'home'
    ));
});

function getBaseURI(){
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT);
}

$app->run();