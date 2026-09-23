<?php

use App\Router;
use App\Controllers\EntregaController;
use App\Controllers\MotivosController;
use App\Controllers\TransportadoraController;

function registerRoutes(Router $router): void
{
    $router->get('/transportadoras',                  [TransportadoraController::class, 'index']);
    $router->post('/transportadoras',                 [TransportadoraController::class, 'store']);
    $router->get('/transportadoras/{id}',             [TransportadoraController::class, 'show']);
    $router->patch('/transportadoras/{id}/desativar', [TransportadoraController::class, 'desativar']);
    $router->patch('/transportadoras/{id}/reativar',  [TransportadoraController::class, 'reativar']);

    $router->get('/entregas',               [EntregaController::class, 'index']);
    $router->post('/entregas',              [EntregaController::class, 'store']);
    $router->get('/entregas/{id}',          [EntregaController::class, 'show']);
    $router->patch('/entregas/{id}/status', [EntregaController::class, 'updateStatus']);
    $router->post('/entregas/{id}/nao-conformidades', [EntregaController::class, 'naoConformidades']);
    $router->get('/entregas/{id}/nao-conformidades',  [EntregaController::class, 'listNaoConformidades']);

    $router->get('/motivos-nao-conformidade', [MotivosController::class, 'index']);
    $router->get('/rastreamento/{codigo}',    [EntregaController::class, 'rastreamento']);
}
