<?php

namespace PiteurStudio\Client;

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimInvalidCredentials;
use PiteurStudio\Exception\SatimUnexpectedResponseException;
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

    private bool $test_mode;

    /**
     * @param  bool  $test_mode  Whether to use the test API or not.
     */
    public function __construct(bool $test_mode = false)
    {
        $this->test_mode = $test_mode;
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
     * This method sends a POST request to the Satim API at the specified endpoint with the given data.
     * It handles various exceptions related to decoding, client, server, or redirection errors.
     * If an exception occurs, it throws a SatimUnexpectedResponseException with a descriptive error message.
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

        $clientOptions = $this->getClientOptions();

        // Create an HTTP client with the specified options
        $httpClient = HttpClient::create($clientOptions);

        // Create a retryable HTTP client with the specified options and maximum retries
        $httpClient = new RetryableHttpClient($httpClient, null, $this->maxRetries);

        try {
            // Send the request and get the response
            $response = $httpClient->request('POST', $url, ['body' => $data]);

            // Try to decode the response to an array
            return $response->toArray(); // This will throw various exceptions if the response is not valid or there's an error
        } catch (DecodingExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            // Handle all possible exceptions related to decoding, client, server, or redirection errors
            $exceptionMessage = match (true) {
                $e instanceof DecodingExceptionInterface => 'Invalid JSON response from the server',
                $e instanceof ClientExceptionInterface => 'Client error occurred (4xx)',
                $e instanceof RedirectionExceptionInterface => 'Redirection error occurred (3xx)',
                $e instanceof ServerExceptionInterface => 'Server error occurred (5xx)'
            };

            throw new SatimUnexpectedResponseException($exceptionMessage.': '.$e->getMessage(), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Network error occurred: '.$e->getMessage());
        }

    }

    /**
     * Returns the client options to be used for the HTTP request.
     *
     * These options are:
     * - timeout: How long to wait for a response from the server.
     * - verify_peer: Whether to verify the SSL certificate of the server.
     * - verify_host: Whether to verify the host name of the server with the SSL certificate.
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
     * If the response contains an error code, throw a SatimUnexpectedResponseException
     * with a descriptive error message.
     *
     * @param  array<string,mixed>  $response  The API response to validate.
     *
     * @throws SatimUnexpectedResponseException|SatimInvalidCredentials if the response contains an error code.
     */
    private function validateApiResponse(array $response): void
    {
        // Check if the response contains for common error codes
        if (isset($response['ErrorCode'])) {

            // ErrorCode: '6' - ErrorMessage: 'Unknown order id'
            if($response['ErrorCode'] === '6'){

                if(isset($response['ErrorMessage']) && $response['ErrorMessage'] === 'Unknown order id'){
                    throw new SatimInvalidArgumentException('Invalid order ID');
                }

            }

            if($response['ErrorCode'] === '5'){

                if(isset($response['ErrorMessage']) && $response['ErrorMessage'] === 'Access denied'){
                    throw new SatimInvalidCredentials('Invalid username or password or terminal ID');
                }

                // Get the error message from the response
                $errorMessage = $response['ErrorMessage'] ?? 'Unknown Error';

                // Throw a SatimUnexpectedResponseException with the error message
                throw new SatimUnexpectedResponseException(
                    'API Error { ErrorCode: '.$response['ErrorCode'].', ErrorMessage: '.$errorMessage.' }'
                );

            }


        }
    }
}
