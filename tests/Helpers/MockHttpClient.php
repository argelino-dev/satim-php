<?php

namespace PiteurStudio\Tests\Helpers;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class MockHttpClient implements HttpClientInterface
{
    private array $responses = [];

    public function addMockResponse(string $url, ResponseInterface $response): void
    {
        $this->responses[$url] = $response;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->responses[$url] ?? throw new \RuntimeException('Unexpected request: '.$url);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        // TODO: Implement stream() method.
    }

    public function withOptions(array $options): static
    {
        // TODO: Implement withOptions() method.
    }
}
