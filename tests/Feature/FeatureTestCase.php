<?php

namespace Tests\Feature;

use App\Database;
use App\JsonResponseException;
use App\Router;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->db = Database::connection();
            $this->db->query('SELECT 1');
        } catch (PDOException $e) {
            $this->markTestSkipped('MySQL indisponível: ' . $e->getMessage());
        }

        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->db) && $this->db->inTransaction()) {
            $this->db->rollBack();
        }

        unset($GLOBALS['__TEST_BODY']);
        parent::tearDown();
    }

    protected function request(string $method, string $uri, array $body = []): array
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $uri;
        $GLOBALS['__TEST_BODY']    = $body;

        $router = new Router();
        registerRoutes($router);

        ob_start();
        try {
            $router->dispatch();
            $output = ob_get_clean();

            return [
                'status' => http_response_code(),
                'body'   => json_decode($output, true),
            ];
        } catch (JsonResponseException $e) {
            ob_end_clean();

            return [
                'status' => $e->status,
                'body'   => $e->payload,
            ];
        }
    }
}
