<?php

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

        // Try to get the success message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default success message
        return $this->getResponse()['params']['respCode_desc'] ?? ($this->getResponse()['actionCodeDescription'] ?? 'Payment was successful');
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

        // Otherwise, try to get the error message from the confirmOrderResponse
        // If it doesn't exist or is empty, return a default error message
        return $this->getResponse()['params']['respCode_desc'] ?? ($this->getResponse()['actionCodeDescription'] ?? 'Payment failed');
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
        if(isset($this->getResponse()['errorCode']) && $this->getResponse()['errorCode'] == '0'){
            return false;
        }

        if (str_contains('Payment is declined', $this->getResponse()['ErrorMessage'])) {
            return true;
        }

        if (isset($this->getResponse()['actionCode']) && $this->getResponse()['actionCode'] == '2003') {
            return true;
        }

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
        if(isset($this->getResponse()['errorCode']) && $this->getResponse()['errorCode'] == '0'){
            return false;
        }

        if (str_contains('Payment is cancelled', $this->getResponse()['ErrorMessage'])) {
            return true;
        }

        // Check if 'actionCode' is set in response data and is '10'
        return isset($this->getResponse()['actionCode'])
            && $this->getResponse()['actionCode'] == '10';
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
