<?php

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;
use App\Auth;

$uri = $_SERVER['REQUEST_URI'];

$method = $_SERVER['REQUEST_METHOD'];

if($_SERVER['HTTP_HOST'] == "localhost" || $_SERVER['HTTP_HOST'] == "192.168.0.105") {
    $uri = str_replace('/cps_api', "", $uri);
}

$collector = new RouteCollector();

App\Session::start();



$collector->get('/', function() {
    $controller = new App\Controllers\HomeController();
    $controller->index();
});

$collector->get('/home', function() {
    $controller = new App\Controllers\HomeController();
    $controller->index();
});

$collector->get('/contatos/{id}', function($id) {
 	$controller = new App\Controllers\ContatoController();
 	$controller->index($id);
});

$collector->get('/contatos', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->index();
});

$collector->put('/contatos/{id}', function($id) {
	$controller = new App\Controllers\ContatoController();
	$controller->update($id);
});

$collector->post('/contatos', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->insert();
});

$collector->post('/login', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->login();
});


$dispatcher =  new Dispatcher($collector->getData());