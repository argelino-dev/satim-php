<?php

namespace PiteurStudio\Client;

use PiteurStudio\Exception\SatimApiException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HttpClientService
{
    private const API_URL = 'https://cib.satim.dz/payment/rest';

    private const TEST_API_URL = 'https://test.satim.dz/payment/rest';

    private int $timeout = 10;

    private int $maxRetries = 3;

    private bool $verifySsl = false;

    private bool $test_mode = false;

    /**
     * HttpClientService constructor.
     */
    public function __construct(bool $test_mode = false)
    {
        $this->test_mode = $test_mode;
    }

    /**
     * Handles the API request and checks for basic errors in the response.
     *
     * @throws SatimApiException
     */
    public function handleApiRequest(string $endpoint, array $data): array
    {
        $result = $this->sendRequest($endpoint, $data);

        // Validate the response structure
        $this->validateApiResponse($result);

        return $result;
    }

    /**
     * Sends the request to the Satim API.
     *
     * @throws SatimApiException
     */
    public function sendRequest(string $endpoint, array $data): array
    {
        $url = $this->getApiUrl().$endpoint;

        $clientOptions = $this->getClientOptions();

        $httpClient = HttpClient::create($clientOptions);
        $httpClient = new RetryableHttpClient($httpClient, null, $this->maxRetries);

        try {
            $response = $httpClient->request('POST', $url, ['body' => $data]);

            // Try to decode the response to an array
            return $response->toArray(); // This will throw various exceptions if the response is not valid or there's an error
        } catch (DecodingExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            // Handle all possible exceptions related to decoding, client, server, or redirection errors
            $exceptionMessage = match (true) {
                $e instanceof DecodingExceptionInterface => 'Invalid JSON response from the server',
                $e instanceof ClientExceptionInterface => 'Client error occurred (4xx)',
                $e instanceof RedirectionExceptionInterface => 'Redirection error occurred (3xx)',
                $e instanceof ServerExceptionInterface => 'Server error occurred (5xx)',
                default => 'Unknown error',
            };

            throw new SatimApiException($exceptionMessage.': '.$e->getMessage(), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Network error occurred: '.$e->getMessage());
        }

    }

    /**
     * Get the appropriate API URL based on the test mode.
     */
    private function getApiUrl(): string
    {
        return $this->test_mode ? self::TEST_API_URL : self::API_URL;
    }

    /**
     * Get client options for the HTTP request.
     */
    private function getClientOptions(): array
    {
        return [
            'timeout' => $this->timeout,
            'verify_peer' => $this->verifySsl,
            'verify_host' => $this->verifySsl,
        ];
    }

    /**
     * Validates the API response and checks for error codes.
     *
     * @throws SatimApiException
     */
    private function validateApiResponse(array $response): void
    {

        if (isset($response['errorCode']) && $response['errorCode'] === '5') {

            $errorMessage = $response['errorMessage'] ?? 'Unknown error';

            throw new SatimApiException('API Error { errorCode: '.$response['errorCode'].', errorMessage: '.$errorMessage.' }');
        }
    }
}
