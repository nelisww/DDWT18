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
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');
$cred = set_cred('ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred) {
    if (!check_cred($cred)){
        $feedback = [
            'type' => 'danger',
            'message' => 'Authentication failed. Please check the credentials.'
        ];
        echo json_encode($feedback);
        exit();
    }
});


// Add routes here
$router->mount('/api', function() use ($router, $db) {
    http_content_type();

    $router->get('/series', function() use ($router, $db) {
        if($_GET['serie_id']) {
            $series_info = get_serieinfo($db, $_GET['serie_id']);
            echo json_encode($series_info);
        } else {
            $series = get_series($db);
            echo json_encode($series);
        }
    });
    $router->delete('/series/', function() use ($db){
        $removed_serie = remove_serie($db, $_GET['serie_id']);
        echo json_encode($removed_serie);
    });
    $router->post('/series/', function() use ($db){
        $added_serie = add_serie($db, $_POST);
        echo json_encode($added_serie);
    });
    $router->put('/series/update', function() use ($db){
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_id = $_GET['serie_id'];
        $serie_info = $_PUT + ["serie_id" => $serie_id];
        $updated_series = update_serie($db, $serie_info);
        echo json_encode($updated_series);
    });
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    // ... do something special here
});

/* Run the router */
$router->run();
