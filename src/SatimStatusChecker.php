<?php

declare(strict_types=1);

namespace PiteurStudio;

use PiteurStudio\Exception\SatimMissingDataException;

trait SatimStatusChecker
{
    /**
     * Get the order status success message
     *
     * @return string The order status success message
     *
     * @throws SatimMissingDataException
     */
    public function getSuccessMessage(): string
    {
        if (! $this->isSuccessful()) {
            return $this->getErrorMessage();
        }

        /** @var array<string, string> $responseData */
        $responseData = $this->getResponse();

        // Try to get the success message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default success message
        return $responseData['params']['respCode_desc'] ?? ($responseData['actionCodeDescription'] ?? 'Payment was successful');
    }

    /**
     * Get the order status error message
     *
     * @return string The order status error message
     *
     * @throws SatimMissingDataException
     */
    public function getErrorMessage(): string
    {
        // If the transaction was rejected, return a translated error message
        if ($this->isRejected()) {
            // TODO: Add more translations
            return '« Votre transaction a été rejetée/ Your transaction was rejected/ تم رفض معاملتك »';
        }

        // If the transaction was refunded, return a success message
        if ($this->isRefunded()) {
            return 'Payment was refunded';
        }

        /** @var array<string, string> $responseData */
        $responseData = $this->getResponse();

        // Otherwise, try to get the error message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default error message
        return $responseData['params']['respCode_desc'] ?? ($responseData['actionCodeDescription'] ?? 'Payment failed');
    }

    /**
     * Check if the transaction was rejected.
     *
     * @return bool True if the transaction was rejected, false otherwise
     *
     * @throws SatimMissingDataException
     */
    public function isRejected(): bool
    {
        /** @var array<string, string> $response */
        $response = $this->getResponse();

        if (isset($response['errorCode']) && $response['errorCode'] === '0') {
            return false;
        }

        if (! empty($response['ErrorMessage']) && str_contains($response['ErrorMessage'], 'Payment is declined')) {
            return true;
        }

        if (isset($response['actionCode']) && $response['actionCode'] === '2003') {
            return true;
        }

        // Ensure refund cases are not considered rejected
        if (isset($response['OrderStatus']) && $response['OrderStatus'] === '4') {
            return false; // Explicitly prevent refunded transactions from being marked as rejected
        }

        // Mark OrderStatus = 3 as rejected (fix)
        if (isset($response['OrderStatus']) && $response['OrderStatus'] === '3') {
            return true;
        }

        // Check that the response data contains the required parameters
        // and that the transaction was rejected
        return (isset($response['params']['respCode']) && $response['params']['respCode'] == '00')
            && $response['ErrorCode'] === '0'
            && $response['OrderStatus'] === '3';
    }

    /**
     * Check if the transaction was successful.
     *
     * This method checks the response data to determine if the transaction
     * was marked as successful by verifying the 'OrderStatus' value.
     *
     * @return bool True if the transaction was successful, false otherwise.
     *
     * @throws SatimMissingDataException
     */
    public function isSuccessful(): bool
    {
        // Check if 'OrderStatus' is set in response data and is either '2' or '0'
        return isset($this->getResponse()['OrderStatus'])
            && ($this->getResponse()['OrderStatus'] == '2' || $this->getResponse()['OrderStatus'] == '0');
    }

    /**
     * Check if the transaction failed.
     *
     * This method will check if the transaction was not marked as
     * successful in the response data.
     *
     * @return bool True if the transaction failed, false otherwise.
     *
     * @throws SatimMissingDataException If the response data is not available.
     */
    public function isFailed(): bool
    {
        return ! $this->isSuccessful() && ! $this->isRefunded();
    }

    /**
     * Check if the transaction was refunded.
     *
     * This method will check if the transaction was refunded by verifying
     * the 'OrderStatus' value.
     *
     * @return bool True if the transaction was refunded, false otherwise.
     *
     * @throws SatimMissingDataException
     */
    public function isRefunded(): bool
    {
        // Check if 'OrderStatus' is set in response data and is '4'
        return isset($this->getResponse()['OrderStatus'])
            && $this->getResponse()['OrderStatus'] == '4';
    }

    /**
     * Check if the transaction was cancelled.
     *
     * This method will check if the transaction was cancelled by verifying
     * the 'actionCode' value in the response data.
     *
     * @return bool True if the transaction was cancelled, false otherwise.
     *
     * @throws SatimMissingDataException
     */
    public function isCancelled(): bool
    {
        /** @var array<string, string> $response */
        $response = $this->getResponse();

        if (isset($response['errorCode']) && $response['errorCode'] === '0') {
            return false;
        }

        if (! empty($response['ErrorMessage']) && str_contains($response['ErrorMessage'], 'Payment is cancelled')) {
            return true;
        }

        // Check if 'actionCode' is set in response data and is '10'
        return isset($response['actionCode'])
            && $response['actionCode'] === '10';
    }

    /**
     * Check if the transaction expired.
     *
     * This method verifies if the transaction has expired by checking
     * the 'actionCode' value in the response data.
     *
     * @return bool True if the transaction expired, false otherwise.
     *
     * @throws SatimMissingDataException
     */
    public function isExpired(): bool
    {
        // Check if 'actionCode' is set in response data and equals '-2007'
        return isset($this->getResponse()['actionCode']) && $this->getResponse()['actionCode'] == '-2007';
    }
}
