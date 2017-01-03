<?php
defined('APPLICATION_ROOT') || define('APPLICATION_ROOT', realpath(dirname(__FILE__) ));

require __DIR__ . '/../vendor/autoload.php';

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

$dbConn = new \Simplon\Mysql\Mysql(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database']
);

$sqlManager = new \Simplon\Mysql\Manager\SqlManager($dbConn);

$fb = new Facebook\Facebook(
    [
        'app_id' => '310902175730143',
        'app_secret' => 'c5e81f0f7d9020775a740023bbb1a4a2',
        'default_graph_version' => 'v2.6'
    ]
);

$accessToken = "EAAEaw42ZBid8BABvtEAxWgHgXWwwdmSJXFxUHf2LOkj8hZC3wq9b0z0rMR7z9BLRlszZBrR7suQyS8m38xAZCuH2cmNOdRXmNnXaEioZCXfH7X3xYynrTSESUZBnmXUebZBs0Q56H2tBHZCNQDzPKVEcOiKDMs1Q2bcZD";
$groups = ['306991289452864', '48915643257', '1038768602805946'];
foreach ( $groups as $groupId ){
    if ( !empty($groupId) ){
        $data = array();
        $message = 'Buscamos Alquilar!';
        $data['link'] = 'http://karolcowan.com/images/alquiler/alquiler_ph.png';
        $data['picture'] = 'http://karolcowan.com/images/alquiler/alquiler_ph.png';

        try {
            $response = $fb->post('/'.$groupId.'/feed', $data, $accessToken);
            var_dump($response);
        } catch (Exception $e){
            echo $e->getMessage();
        }
    }
}
