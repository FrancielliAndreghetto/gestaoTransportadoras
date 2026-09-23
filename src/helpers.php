<?php

use App\JsonResponseException;

function json(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (defined('TESTING') && TESTING) {
        throw new JsonResponseException($data, $status);
    }

    exit;
}

function body(): array
{
    if (defined('TESTING') && TESTING && array_key_exists('__TEST_BODY', $GLOBALS)) {
        return $GLOBALS['__TEST_BODY'] ?? [];
    }

    return json_decode(file_get_contents('php://input'), true) ?? [];
}
