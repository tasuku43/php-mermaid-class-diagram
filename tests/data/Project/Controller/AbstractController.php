<?php
declare(strict_types=1);

namespace TestProject\Controller;

abstract class AbstractController
{
    protected function jsonResponse(array $data): string
    {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    protected function redirectTo(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    abstract protected function handleRequest(): void;
}
