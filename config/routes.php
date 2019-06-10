<?php

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;
use App\Auth;
use App\Http;

$uri = $_SERVER['REQUEST_URI'];

$method = $_SERVER['REQUEST_METHOD'];

if($_SERVER['HTTP_HOST'] == "localhost" || $_SERVER['HTTP_HOST'] == "192.168.0.106") {
    $uri = str_replace('/cps_api', "", $uri);
}

$collector = new RouteCollector();

$collector->filter('auth', function(){
    if(!Auth::verifyJwtToken()) {
    	header('Location: access-denied');
        Http::jsonResponse(false, "Acesso Negado!");
    }
});

$collector->post('/login', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->login();
});

$collector->post('/contatos', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->insert();
});

$collector->get('/email', function() {
	$controller = new App\Controllers\ContatoController();
	$controller->sendEmailToAllUsers();
});

$collector->get('/access-denied', function() {
	echo "
	<h1 style='color: #AA0011; margin-top: 50px; font-family: sans-serif; text-align:center'>Acesso Negado!</h1>";
});

$collector->get('contatos/access-denied', function() {
	echo "
	<h1 style='color: #AA0011; margin-top: 50px; font-family: sans-serif; text-align:center'>Acesso Negado!</h1>";
	
});

$collector->group(['before' => 'auth'], function(RouteCollector $collector){

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

	$collector->delete('/contatos/{id}', function($id) {
		$controller = new App\Controllers\ContatoController();
		$controller->delete($id);
	});

	$collector->delete('/contatos/definitive/{id}', function($id) {
		$controller = new App\Controllers\ContatoController();
		$controller->deleteDefinitively($id);
	});

	$collector->put('/contatos/accept/{id}', function($id) {
		$controller = new App\Controllers\ContatoController();
		$controller->acceptContact($id);
	});

	$collector->get('/contatos/latest', function() {
		$controller = new App\Controllers\ContatoController();
		$controller->getNewContacts();
	});

});

$dispatcher =  new Dispatcher($collector->getData());