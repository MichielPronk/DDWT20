<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */


/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt20_week3', 'ddwt20', 'ddwt20');

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here
header(http_content_type('application/json'));

$cred = set_cred('ddwt20', 'ddwt20');

$router->get('/api/series/', function() use ($cred, $db, $router){
    if (!check_cred($cred)){
        echo 'Authentication required.';
        http_response_code(401);
        die();
    }
    echo "Succesfully authenticated";
});

$router->get('/series', function() use($db) {
    $series = get_series($db);
    $series = json_encode($series);
    echo $series;
});

$router->get('/series/(\d+)', function($id) use($db) {
    $info = get_serieinfo($db, $id);
    $info = json_encode($info);
    echo $info;
});

$router->get('/delete/(\d+)', function($id) use($db) {
    $feedback = remove_serie($db, $id);
    $feedback = json_encode($feedback['message']);
    echo $feedback;
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo 'This page was not found';});
/* Run the router */
$router->run();
