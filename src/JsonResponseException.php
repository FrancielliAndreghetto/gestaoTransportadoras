<?php

namespace App;

use RuntimeException;

class JsonResponseException extends RuntimeException
{
    public function __construct(
        public readonly mixed $payload,
        public readonly int $status,
    ) {
        parent::__construct('json');
    }
}
