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
    'debug'         => true
    //'cache' => dirname(__FILE__) . '/../cache'
);

$cloudinary = new Twig_SimpleFilter('cloudinary', function ($path, $params = array()) {
    \Cloudinary::config(array(
        "cloud_name" => "dplksnehy",
        "api_key" => "586718325429517",
        "api_secret" => "YbnnVUyNLna_zRDKDGPr3VtigPg"
    ));

    $params['quality'] = '80';
    $params['flags'] = "lossy";
    $params['version'] = "1"; //LAST version

    $url = cloudinary_url($path, $params);
    return $url;
});

$clImgTag = new Twig_SimpleFilter('cl_image_tag', function ($path, $params = array()) {
    \Cloudinary::config(array(
        "cloud_name" => "dplksnehy",
        "api_key" => "586718325429517",
        "api_secret" => "YbnnVUyNLna_zRDKDGPr3VtigPg"
    ));

    //$params['type'] = 'fetch';
    $params['quality'] = '80';
    $params['flags'] = "lossy";
    $params['version'] = "1"; //LAST version

    $img = cl_image_tag($path, $params);
    return $img;
});

$imageWidth = new Twig_SimpleFilter('image_width', function($path){
    try {
        list($width, $height) = getimagesize($path);
        return $width;
    } catch (Exception $e){
        return '';
    }

    return '';
});

$imageHeight = new Twig_SimpleFilter('image_height', function($path){
    try {
        list($width, $height) = getimagesize($path);
        return $height;
    } catch (Exception $e){
        return '';
    }

    return '';
});

$twig = $view->getEnvironment();
$twig->addFilter($cloudinary);
$twig->addFilter($clImgTag);
$twig->addFilter($imageWidth);
$twig->addFilter($imageHeight);

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
    var_dump($e->getMessage());
});


$app->notFound(function (\Exception $e) use ($app) {
    var_dump($e->getMessage());
});


$app->hook('slim.before', function() use ($app) {

});

$app->get('/', function() use ($app) {
    echo $app->view->render('home.html', array(
        'tab' => 'home'
    ));
});

$app->get('/cv', function() use ($app) {
    echo $app->view->render('cv.html', array(
        'tab' => 'cv'
    ));
});

$app->get('/portfolio', function() use ($app, $dbConn) {
    \Cloudinary::config(array(
        "cloud_name" => "dplksnehy",
        "api_key" => "586718325429517",
        "api_secret" => "YbnnVUyNLna_zRDKDGPr3VtigPg"
    ));

    $api = new \Cloudinary\Api();

    $tResponse = $api->tags();
    $tResponse = $tResponse->getArrayCopy();
    $pictures = array();

    $images = $dbConn->fetchRowMany('SELECT * FROM images');
    foreach ( $images as $image ){
        array_push($pictures, $image);
    }

    echo $app->view->render('portfolio.html', array(
        'tab'           => 'portfolio',
        'pictures'      => $pictures,
        'categories'    => $tResponse['tags']
    ));
});

$app->get('/eventos/social', function() use ($app) {
    echo $app->view->render('eventos/social.html', array(
        'tab' => 'home'
    ));
});

$app->get('/eventos/bailarin', function() use ($app) {
    echo $app->view->render('eventos/bailarin.html', array(
        'tab' => 'home'
    ));
});

$app->get('/eventos/clases', function() use ($app) {
    echo $app->view->render('eventos/clases.html', array(
        'tab' => 'home'
    ));
});

$app->get('/eventos/music', function() use ($app) {
    echo $app->view->render('eventos/musico.html', array(
        'tab' => 'home'
    ));
});

$app->get('/admin/upload-images', function() use ($app, $dbConn) {
    $images = $dbConn->fetchRowMany('SELECT * FROM images');
    echo $app->view->render('admin/upload_images.html', array(
        'pictures' => $images
    ));
});

$app->post('/admin/upload-images', function() use ($app, $dbConn) {
    \Cloudinary::config(array(
        "cloud_name" => "dplksnehy",
        "api_key" => "586718325429517",
        "api_secret" => "YbnnVUyNLna_zRDKDGPr3VtigPg"
    ));

    $api = new \Cloudinary\Api();

    $data = $app->request->post();
    foreach ( $data['images'] as $image ){
        if ( !empty($image['delete']) &&  $image['delete'] == 'on'){
            //Delete from database
            $result = $dbConn->delete('images', array('id' => $image['id']));

            //Delete fron cloudinary
            $api->delete_resources( array($image['public_id'] ));
        }

        if ( !empty($image['id']) ){ //update
            $data = array(
                'title'         => $image['title'],
                'description'   => $image['description'],
            );
            $result = $dbConn->update('images', array('id' => $image['id']), $data);
        } else { //NEW

            //Save URl into database
            $image = array(
                array(
                    'id'            => false,
                    'public_id'           => $image['public_id'],
                    'url'           => $image['url'],
                    'category'      => $image['category'],
                    'title'         => $image['title'],
                    'description'   => $image['description'],
                )
            );
            $id = $dbConn->insertMany('images', $image);
        }
    }

    $app->redirect('/admin/upload/images');
});

$app->run();