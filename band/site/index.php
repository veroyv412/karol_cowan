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
    echo $app->view->render('404.twig', array(
        'message' => $e->getMessage()
    ));
});

$app->notFound(function (\Exception $e) use ($app) {
    echo $app->view->render('404.twig', array(
        'message' => $e->getMessage()
    ));
});


$app->hook('slim.before', function() use ($app) {
    $baseUrl = getBaseURI();

    $data['base_url'] = $baseUrl;
    $app->view()->appendData($data);
});

$app->get('/', function() use ($app, $dbConn) {
    $upcoming_events = $dbConn->fetchRowMany('SELECT * FROM events WHERE new = 1');
    $passed_events = $dbConn->fetchRowMany('SELECT * FROM events WHERE new = 0');
    
    echo $app->view->render('home.twig', array(
        'tab'               => 'home',
        'upcoming_events'   => $upcoming_events,
        'passed_events'     => $passed_events
    ));
});

$app->get('/members', function() use ($app, $dbConn) {
    echo $app->view->render('members.twig', array(
        'tab'               => 'members',
        
    ));
});

$app->get('/events', function() use ($app, $dbConn) {
    $events = $dbConn->fetchRowMany('SELECT * FROM events ORDER BY new DESC');
    echo $app->view->render('events.twig', array(
        'tab'               => 'events',
        'events'            => $events
    ));
});

$app->get('/events/:event_id', function($event_id) use ($app, $dbConn) {
    $event = $dbConn->fetchRow('SELECT * FROM events WHERE id = ' . $event_id);

    $images = [];
    foreach(glob(APPLICATION_ROOT.'/images/event/event_'.$event_id.'/*.*') as $file) {
        array_push($images, '/images/event/event_'.$event_id.'/'.basename($file));
    }

    echo $app->view->render('event-detail.twig', array(
        'tab'               => 'events',
        'event'             => $event,
        'images'            => $images
    ));
});

$app->get('/gallery(/)', function() use ($app) {
    echo $app->view->render('gallery.twig', array(
        'tab'               => 'gallery'
    ));
});

$app->get('/contact(/)', function() use ($app) {
    $message = !empty($_SESSION['contact_thankyou']) ? $_SESSION['contact_thankyou'] : null;
    if ( isset($_SESSION['contact_thankyou']) ){
        unset($_SESSION['contact_thankyou']);
    }
    echo $app->view->render('contact.twig', array(
        'tab'               => 'contact',
        'contact_thankyou'  => !empty($message) ? $message : null
    ));
});

$app->post('/contact', function() use ($app){
    $data = $app->request->post();
    
    $html = $app->view->fetch('contact_email.twig', array(
        'data' => $data
    ));

    $transport = new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
    $transport->setUsername('veroyv412@gmail.com');
    $transport->setPassword('v3r0n1c4');

    $mailer = new Swift_Mailer($transport);
    $message = (new Swift_Message('BANDA | Contactenos'))
        ->setFrom($data['email'], $data['full_name'])
        ->setContentType('text/html')
        ->setTo(array('veroyv412@gmail.com' => 'Karol Cowan'))
        ->setBody($html);
    $numSent = $mailer->send($message);

    $message = (new Swift_Message('Suscripcion a Clases Particulares'))
        ->setFrom('loraknawoc@hotmail.com', 'Karol Cowan')
        ->setContentType('text/html')
        ->setTo($data['email'],  $data['full_name'])
        ->setBody($html);
    $numSent = $mailer->send($message);

    $_SESSION['contact_thankyou'] = 'Gracias por contactarnos, hemos recibido su solicitud. Nos contactaremos a la brevedad!';
    $app->redirect('/contact');
});

function getBaseURI(){
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT);
}

$app->run();