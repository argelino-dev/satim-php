<?php

namespace PiteurStudio;

use PiteurStudio\Exception\SatimInvalidDataException;
use PiteurStudio\Exception\SatimMissingDataException;

trait SatimStatusChecker
{
    protected ?array $confirmOrderResponse = null;

    /**
     * Get the order status success message
     *
     * @return string The order status success message
     *
     * @throws SatimMissingDataException
     */
    public function getSuccessMessage(): string
    {
        // Try to get the success message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default success message
        return $this->getResponse()['params']['respCode_desc'] ?? ($this->getResponse()['actionCodeDescription'] ?? 'Payment was successful');
    }

    /**
     * Get the order status error message
     *
     * @return string The order status error message
     *
     * @throws SatimInvalidDataException
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

        // Otherwise, try to get the error message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default error message
        return $this->getResponse()['params']['respCode_desc'] ?? ($this->getResponse()['actionCodeDescription'] ?? 'Payment failed');
    }

    /**
     * Ensure that the response data is available before performing any status checks.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    protected function ensureDataIsAvailable(): void
    {
        // Check that the response data is available
        if (! isset($this->response_data)) {
            // If the response data is not available, throw an exception
            throw new SatimInvalidDataException(
                'No data available: call confirm() or status() first.'
            );
        }
    }

    /**
     * Check if the transaction was rejected.
     *
     * @return bool True if the transaction was rejected, false otherwise
     *
     * @throws SatimInvalidDataException
     */
    public function isRejected(): bool
    {
        $this->ensureDataIsAvailable();

        // Check that the response data contains the required parameters
        // and that the transaction was rejected
        return (isset($this->response_data['params']['respCode']) && $this->response_data['params']['respCode'] == '00')
            && $this->response_data['ErrorCode'] == '0'
            && $this->response_data['OrderStatus'] == '3';
    }

    /**
     * Check if the transaction was successful.
     *
     * This method checks the response data to determine if the transaction
     * was marked as successful by verifying the 'OrderStatus' value.
     *
     * @return bool True if the transaction was successful, false otherwise.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    public function isSuccessful(): bool
    {
        $this->ensureDataIsAvailable();

        // Check if 'OrderStatus' is set in response data and is either '2' or '0'
        return isset($this->response_data['OrderStatus'])
            && ($this->response_data['OrderStatus'] == '2' || $this->response_data['OrderStatus'] == '0');
    }

    /**
     * Check if the transaction failed.
     *
     * This method will check if the transaction was not marked as
     * successful in the response data.
     *
     * @return bool True if the transaction failed, false otherwise.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    public function isFailed(): bool
    {
        $this->ensureDataIsAvailable();

        return ! $this->isSuccessful();
    }

    /**
     * Check if the transaction was refunded.
     *
     * This method will check if the transaction was refunded by verifying
     * the 'OrderStatus' value.
     *
     * @return bool True if the transaction was refunded, false otherwise.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    public function isRefunded(): bool
    {
        $this->ensureDataIsAvailable();

        // Check if 'OrderStatus' is set in response data and is '4'
        return isset($this->response_data['OrderStatus'])
            && $this->response_data['OrderStatus'] == '4';
    }

    /**
     * Check if the transaction was cancelled.
     *
     * This method will check if the transaction was cancelled by verifying
     * the 'actionCode' value in the response data.
     *
     * @return bool True if the transaction was cancelled, false otherwise.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    public function isCancelled(): bool
    {
        $this->ensureDataIsAvailable();

        // Check if 'actionCode' is set in response data and is '10'
        return isset($this->response_data['actionCode'])
            && $this->response_data['actionCode'] == '10';
    }

    /**
     * Check if the transaction expired.
     *
     * This method verifies if the transaction has expired by checking
     * the 'actionCode' value in the response data.
     *
     * @return bool True if the transaction expired, false otherwise.
     *
     * @throws SatimInvalidDataException If the response data is not available.
     */
    public function isExpired(): bool
    {
        $this->ensureDataIsAvailable();

        // Check if 'actionCode' is set in response data and equals '-2007'
        return isset($this->response_data['actionCode']) && $this->response_data['actionCode'] == '-2007';
    }
}
