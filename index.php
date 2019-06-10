<?php  
header('Access-Control-Allow-Methods: GET');

require 'vendor\autoload.php';

require 'config\routes.php';

use App\Session;
use App\Cookie;


try {
	echo $dispatcher->dispatch($method, $uri);
} catch(Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
	echo "<h1>Erro 404</h1>";
	http_response_code(404);
}
