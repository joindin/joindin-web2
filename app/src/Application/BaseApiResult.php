<?php

declare(strict_types=1);

namespace Application;

class BaseApiResult
{
    private array $headers;

    private int $statusCode;

    private string $body;

    public function __construct(string $body, int $statusCode, array $headers)
    {
        $this->body       = $body;
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
    }

    public function get_headers(): array
    {
        return $this->headers;
    }

    public function get_status_code(): int
    {
        return $this->statusCode;
    }

    public function get_body(): string
    {
        return $this->body;
    }
}
