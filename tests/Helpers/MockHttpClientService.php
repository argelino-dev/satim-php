<?php

namespace PiteurStudio\Tests\Helpers;

use PiteurStudio\Client\HttpClientService;

class MockHttpClientService extends HttpClientService
{
    private array $mockResponses = [];

    public function __construct()
    {
        // Disable real API calls
    }

    public function addMockResponse(string $endpoint, array $response): void
    {
        $this->mockResponses[$endpoint] = $response;
    }

    public function handleApiRequest(string $endpoint, array $data): array
    {
        return $this->mockResponses[$endpoint] ?? throw new \RuntimeException('Unexpected request: '.$endpoint);
    }
}
