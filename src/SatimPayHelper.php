<?php

namespace PiteurStudio;

use JetBrains\PhpStorm\NoReturn;
use PiteurStudio\Exception\SatimMissingDataException;

trait SatimPayHelper
{
    private ?array $registerOrderResponse = null;

    /**
     * Retrieves the response data for the registered order.
     *
     * This method retrieves the response data associated with the registered order.
     * The response data is stored when the registerOrder() method is called.
     * If registerOrder() was not called before calling this method, a SatimMissingDataException is thrown.
     *
     * @return array The response data associated with the registered order.
     * @throws SatimMissingDataException If the registerOrder() was not called first.
     */
    public function getResponse(): array
    {
        if (empty($this->registerOrderResponse)) {
            throw new SatimMissingDataException(
                'No payment data found. Call registerOrder() first to register the order and obtain the response data.'
            );
        }

        return $this->registerOrderResponse;
    }

    /**
     * Retrieves the order ID from the response data.
     *
     * This method extracts the order ID from the response data obtained from the Satim API.
     * It relies on the getResponse() method to retrieve the response data.
     *
     * @return int The order ID extracted from the response data.
     * @throws SatimMissingDataException If the registerOrder() was not called first.
     */
    public function getOrderId(): int
    {
        // Retrieve the response data from the getResponse() method
        $responseData = $this->getResponse();

        // Extract the order ID from the response data
        return $responseData['orderId'];
    }

    /**
     * Redirects the user to the URL specified in the response data.
     *
     * This method sends an HTTP header to perform the redirection and terminates the script execution.
     * This method should be used after the registerOrder() method has been called to redirect the user to the
     * payment form.
     *
     * @return void
     * @throws SatimMissingDataException If the registerOrder() was not called first.
     */
    #[NoReturn]
    public function redirect(): void
    {
        // Retrieve the URL from the getResponse() method
        $url = $this->getUrl();

        // Set the HTTP header for redirection
        header('Location: '.$url);

        // Terminate the script execution to prevent any further code execution
        exit;
    }

    /**
     * Retrieves the URL of the payment form from the response data.
     *
     * @return string The URL of the payment form associated with the response data.
     * @throws SatimMissingDataException If the registerOrder() was not called first.
     */
    public function getUrl(): string
    {
        // Retrieve the response data from the getResponse() method
        $responseData = $this->getResponse();

        // Extract the URL from the response data
        return $responseData['formUrl'];
    }
}
