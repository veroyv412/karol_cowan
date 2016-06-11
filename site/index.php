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
    echo $app->view->render('home.html', array(
        'tab' => 'home'
    ));
});


$app->get('/fb', function() use ($app){
    /*$fb = new Facebook\Facebook(
        [
            'app_id' => '1669048966705838',
            'app_secret' => '6328253793bd8e6eb960745f2dd937ed',
            'default_graph_version' => 'v2.6'
        ]
    );*/

    $fb = new Facebook\Facebook(
        [
            'app_id' => '310902175730143',
            'app_secret' => 'c5e81f0f7d9020775a740023bbb1a4a2',
            'default_graph_version' => 'v2.6'
        ]
    );

    $permissions = ['email', 'publish_actions']; // Optional permissions
    $helper = $fb->getRedirectLoginHelper();
    $loginUrl = $helper->getLoginUrl('http://karolcowan.com/fb-callback', $permissions);

    echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
});

$app->get('/fb-callback', function() use ($app){
    $fb = new Facebook\Facebook(
        [
            'app_id' => '310902175730143',
            'app_secret' => 'c5e81f0f7d9020775a740023bbb1a4a2',
            'default_graph_version' => 'v2.6'
        ]
    );

    $helper = $fb->getRedirectLoginHelper();

    try {
        $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    if (! isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }

    // Logged in
    echo '<h3>Access Token</h3>';
    var_dump($accessToken->getValue());
    
    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();
    
    // Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    echo '<h3>Metadata</h3>';
    var_dump($tokenMetadata);
    
    // Validation (these will throw FacebookSDKException's when they fail)
    $tokenMetadata->validateAppId('310902175730143'); // Replace {app-id} with your app id
    // If you know the user ID this access token belongs to, you can validate it here
    //$tokenMetadata->validateUserId('123');
    $tokenMetadata->validateExpiration();
    
    if (! $accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
            exit;
        }
    
        echo '<h3>Long-lived</h3>';
        var_dump($accessToken->getValue());
    }
    
    $_SESSION['fb_access_token'] = (string) $accessToken;

    $app->response->redirect('/fb-autopost');
});


$app->get('/fb-autopost', function() use ($app){
    $fb = new Facebook\Facebook(
        [
            'app_id' => '310902175730143',
            'app_secret' => 'c5e81f0f7d9020775a740023bbb1a4a2',
            'default_graph_version' => 'v2.6'
        ]
    );

    if ( !empty($_SESSION['fb_access_token']) ){
        try {
            print_r($_SESSION['fb_access_token']);
            
            $me = $fb->get('me', $_SESSION['fb_access_token']);
            print_r($me);

            $response = $fb->post('/1178672095511260/feed', array('message' => 'tesss'), $_SESSION['fb_access_token']);
            print_r($response);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo "Exception occured, code: " . $e->getCode();
            echo " with message: " . $e->getMessage();
        }
    }
});

$app->get('/cv(/)', function() use ($app) {
    echo $app->view->render('cv.html', array(
        'tab' => 'cv'
    ));
});

$app->get('/portfolio(/)', function() use ($app, $dbConn) {

    $pictures = array();
    $images = $dbConn->fetchRowMany('SELECT * FROM images');
    foreach ( $images as $image ){
        $image['url'] = '/images/gallery/' . lcfirst($image['category']) . '/' . basename($image['url']);
        array_push($pictures, $image);
    }

    echo $app->view->render('portfolio.html', array(
        'tab'           => 'portfolio',
        'pictures'      => $pictures,
        'categories'    => array('Bailarin', 'Eventos', 'Musico', 'Profesor')
    ));
});

$app->get('/eventos/social(/)', function() use ($app) {
    echo $app->view->render('eventos/social.html', array(
        'tab' => 'eventos'
    ));
});

$app->get('/eventos/bailarin(/)', function() use ($app) {
    echo $app->view->render('eventos/bailarin.html', array(
        'tab' => 'eventos'
    ));
});

$app->get('/eventos/clases(/)', function() use ($app) {
    echo $app->view->render('eventos/clases.html', array(
        'tab' => 'profesor'
    ));
});

$app->get('/eventos/noche-jueves(/)', function() use ($app) {
    echo $app->view->render('eventos/noche_jueves.html', array(
        'tab' => 'eventos'
    ));
});


$app->get('/eventos/music(/)', function() use ($app) {
    echo $app->view->render('eventos/musico.html', array(
        'tab' => 'eventos'
    ));
});


$app->get('/propuestas(/)', function() use ($app) {
    echo $app->view->render('propuestas/index.html', array(
        'tab' => 'eventos'
    ));
});

$app->get('/suscripciones(/)', function() use ($app) {
    $suscripcion_thankyou = !empty($_SESSION['suscripcion_thankyou']) ? $_SESSION['suscripcion_thankyou'] : null;
    unset($_SESSION['suscripcion_thankyou']);
    echo $app->view->render('suscripciones.html', array(
        'tab' => 'profesor',
        'suscripcion_thankyou' => $suscripcion_thankyou
    ));
});

$app->post('/suscripciones', function() use ($app, $dbConn) {
    $data = $app->request->post();

    $html = $app->view->fetch('suscripcion_email.html', array(
        'data' => $data
    ));

    $transport = new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
    $transport->setUsername('veroyv412@gmail.com');
    $transport->setPassword('v3r0n1c4');

    $mailer = new Swift_Mailer($transport);
    $message = (new Swift_Message('Suscripcion a Clases'))
        ->setFrom($data['email'], $data['name'])
        ->setContentType('text/html')
        ->setTo(array('veroyv412@gmail.com' => 'Karol Cowan'))
        ->setBody($html);
    $numSent = $mailer->send($message);

    $message = (new Swift_Message('Suscripcion a Clases'))
        ->setFrom('cowan.karol@gmail.com', 'Karol Cowan')
        ->setContentType('text/html')
        ->setTo($data['email'],  $data['name'])
        ->setBody($html);
    $numSent = $mailer->send($message);

    $image = array(
        array(
            'id'            => false,
            'name'          => $data['name'],
            'email'         => $data['email'],
            'classes'       => json_encode($data['clases']),
            'purpose'       => json_encode($data['tomar_clases']),
            'message'       => $data['comment']
        )
    );
    $id = $dbConn->insertMany('suscriptions', $image);


    $_SESSION['suscripcion_thankyou'] = 'Gracias por suscribirte, te hemos enviado un mail con toda esta informacion para que te quede agendado. Te esperamos!';
    $app->redirect('/suscripciones');
});

$app->get('/contact(/)', function() use ($app) {
    echo $app->view->render('contact.html', array(
        'tab' => 'contact'
    ));
});

$app->get('/admin/upload-images(/)', function() use ($app, $dbConn) {
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
                    'public_id'     => $image['public_id'],
                    'url'           => $image['url'],
                    'category'      => !empty($image['category']) ? $image['category'] : '',
                    'title'         => !empty($image['title']) ? $image['title'] : 'Artista Cubano',
                    'description'   => !empty($image['description']) ? $image['description'] : 'Karol Cowan',
                )
            );
            $id = $dbConn->insertMany('images', $image);
        }
    }

    $app->redirect('/admin/upload-images');
});

$app->get('/sales(/)', function() use ($app, $dbConn) {
    \Cloudinary::config(array(
        "cloud_name" => "dplksnehy",
        "api_key" => "586718325429517",
        "api_secret" => "YbnnVUyNLna_zRDKDGPr3VtigPg"
    ));

    $api = new \Cloudinary\Api();

    /* Start  of getting images according prefix and saving into database */
    /*$list = $api->resources(array("type" => "upload", "prefix" => "veronica/sales/teclado"));
    $images = array();
    foreach ($list['resources'] as $item){
        $image = array(
            'id'            => false,
            'sale_id'       => 5,
            'public_id'     => $item['public_id'],
            'image'         => $item['url']
        );

        array_push($images, $image);
    }
    $id = $dbConn->insertMany('sales_images', $images);*/
    /* END */


    $products = $dbConn->fetchRowMany('SELECT * FROM sales ORDER BY sort ASC;');
    echo $app->view->render('/sales/index.html', array(
        'products' => $products
    ));
});

$app->get('/sales/product/:product_id', function($product_id) use ($app, $dbConn) {
    $product = $dbConn->fetchRow('SELECT * FROM sales WHERE id = '. $product_id. ';');
    if ( !empty($product) ){
        $images = $dbConn->fetchRowMany('SELECT * FROM sales_images WHERE sale_id = '. $product_id. ';');
        $product['images'] = $images;

        echo $app->view->render('/sales/single.html', array(
            'product' => $product
        ));
    } else {
        echo 'ERROR. EL ID del producto no corresponde a un producto almacenado en la base de datos.';
    }

});

function getBaseURI(){
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'],PHP_URL_FRAGMENT);
}

$app->run();