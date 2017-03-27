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

$groups = $dbConn->fetchRowMany('SELECT * FROM groups order by group_category DESC');
foreach ( $groups as $group ){
    $post = $dbConn->fetchRow('SELECT * FROM posts WHERE id = 1');
    if ( !empty($post) ){
        $data = array();
        $message = $post['post_title'];
        if ( !empty($post['post_description']) ){
            $message .= ' ' . $post['post_description'];
        }
        $data['message'] = $message;
        $data['link'] = $post['post_link'];
        //$data['picture'] = $post['post_picture'];

        try {
            $response = $fb->post('/'.$group['group_id'].'/feed', $data, $accessToken);
            //$response = $fb->post('/1178672095511260/feed', $data, $accessToken);
            var_dump($response);
            sleep(60);
        } catch (Exception $e){
            echo $e->getMessage();
        }

    }
}