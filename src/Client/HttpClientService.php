<?php

namespace PiteurStudio\Client;

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimInvalidCredentials;
use PiteurStudio\Exception\SatimUnexpectedResponseException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientService
{
    private const API_URL = 'https://cib.satim.dz/payment/rest';

    private const TEST_API_URL = 'https://test.satim.dz/payment/rest';

    private int $timeout = 10;

    private int $maxRetries = 3;

    private bool $verifySsl = false;

    private readonly HttpClientInterface $httpClient;

    /**
     * @param  bool  $test_mode  Whether to use the test API or not.
     * @param  HttpClientInterface|null  $httpClient  Injected HTTP client for testing (optional).
     */
    public function __construct(private readonly bool $test_mode = false, ?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new RetryableHttpClient(HttpClient::create($this->getClientOptions()), null, $this->maxRetries);
    }

    /**
     * Get the API base URL.
     *
     * @return string The API base URL.
     */
    private function getApiUrl(): string
    {
        return $this->test_mode ? self::TEST_API_URL : self::API_URL;
    }

    /**
     * Handles the API request by sending it to the specified endpoint with the given data.
     * Validates the response and checks for any basic errors.
     *
     * @param  string  $endpoint  The API endpoint to send the request to.
     * @param  array<string,mixed>  $data  The data to send with the request.
     * @return array<string,mixed> The response from the API.
     *
     * @throws SatimUnexpectedResponseException|SatimInvalidCredentials If the response contains an error.
     */
    public function handleApiRequest(string $endpoint, array $data): array
    {
        // Send the request to the API and get the result
        $result = $this->sendRequest($endpoint, $data);

        // Validate the response structure and check for errors
        $this->validateApiResponse($result);

        // Return the validated response
        return $result;
    }

    /**
     * Sends the request to the Satim API.
     *
     * @param  string  $endpoint  The API endpoint to send the request to.
     * @param  array<string,mixed>  $data  The data to send with the request.
     * @return array<string,mixed> The response from the API.
     *
     * @throws SatimUnexpectedResponseException If an unexpected error occurs.
     */
    public function sendRequest(string $endpoint, array $data): array
    {
        $url = $this->getApiUrl().$endpoint;

        try {
            $response = $this->httpClient->request('POST', $url, ['body' => $data]);

            return $response->toArray(); // This will throw exceptions if the response is invalid
        } catch (DecodingExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new SatimUnexpectedResponseException('API Error: '.$e->getMessage(), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new SatimUnexpectedResponseException('Network error: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Returns the client options to be used for the HTTP request.
     *
     * @return array<string,mixed> The client options.
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
     * @param  array<string,mixed>  $response  The API response to validate.
     *
     * @throws SatimUnexpectedResponseException|SatimInvalidCredentials If the response contains an error.
     */
    private function validateApiResponse(array $response): void
    {
        if (isset($response['ErrorCode'])) {
            if ($response['ErrorCode'] === '6' && $response['ErrorMessage'] === 'Unknown order id') {
                throw new SatimInvalidArgumentException('Invalid order ID');
            }

            if ($response['ErrorCode'] === '5' && (isset($response['ErrorMessage']) && $response['ErrorMessage'] === 'Access denied')) {
                throw new SatimInvalidCredentials('Invalid username or password or terminal ID');
            }

            throw new SatimUnexpectedResponseException('API Error { ErrorCode: '.$response['ErrorCode'].', ErrorMessage: '.($response['ErrorMessage'] ?? 'Unknown Error').' }');
        }
    }
}
