<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Middlewares\JsonMiddleware;
use \Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

use App\Controllers\UsuarioController;
use App\Controllers\PedidoController;
use App\Middlewares\AuthAdminMiddleware;
use Config\Database;

new Database;


$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$app->setBasePath('/Programacion3/TPLaComanda/public');



//$app->put('/mesa/{idMesa}', PedidoController::class.':cerrarMesa');

//$app->post('/pedido/{idMesa}', PedidoController::class.':addPedido');





//$app->put('/estado/{idPedido}', PedidoController::class.':editEstado');

$app->post('/registro', UsuarioController::class.':add');

$app->post('/login', UsuarioController::class.':login');

$app->get('/pendientes', PedidoController::class.':getPendientes');


$app->group('/mesa', function (RouteCollectorProxy $group) {
    
    $group->post('[/]', PedidoController::class.':addMesa');

    $group->post('/{idMesa}', PedidoController::class.':cerrarMesa')->add(new AuthAdminMiddleware);

});


$app->group('/pedido', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', PedidoController::class.':getPedido');

    $group->post('/{idMesa}', PedidoController::class.':addPedido');

});

$app->group('/estado', function (RouteCollectorProxy $group) {
    
    $group->put('/{idPedido}', PedidoController::class.':prepararPedido');

    $group->post('/{idPedido}', PedidoController::class.':servirPedido');

});

$app->add(new JsonMiddleware);



$app->run();

