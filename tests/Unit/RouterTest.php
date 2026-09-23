<?php

namespace Tests\Unit;

use App\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RouterProbe::$params = [];
        http_response_code(200);
    }

    public function testEncontraRotaComParametro(): void
    {
        $router = new Router();
        $router->get('/rastreamento/{codigo}', [RouterProbe::class, 'capture']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/rastreamento/BRD-2024-00001';

        $router->dispatch();

        $this->assertSame(['codigo' => 'BRD-2024-00001'], RouterProbe::$params);
    }

    public function testRetorna404ParaRotaInexistente(): void
    {
        $router = new Router();
        $router->get('/entregas', [RouterProbe::class, 'capture']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/nao-existe';

        ob_start();
        $router->dispatch();
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertSame(['erro' => 'Rota não encontrada'], json_decode($output, true));
    }

    public function testNaoCasaMetodoDiferente(): void
    {
        $router = new Router();
        $router->get('/entregas/1/nao-conformidades', [RouterProbe::class, 'capture']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/entregas/1/nao-conformidades';

        ob_start();
        $router->dispatch();
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertSame(['erro' => 'Rota não encontrada'], json_decode($output, true));
    }
}

class RouterProbe
{
    public static array $params = [];

    public static function capture(array $params): void
    {
        self::$params = $params;
    }
}
