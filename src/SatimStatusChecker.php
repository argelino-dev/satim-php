<?php

namespace PiteurStudio;

use PiteurStudio\Exception\SatimInvalidDataException;

trait SatimStatusChecker
{
    protected ?array $response_data = null;

    /**
     * Get the order status success message
     *
     * @return string
     * @throws SatimInvalidDataException
     */
    public function getSuccessMessage(): string
    {
        return $this->getData()['params']['respCode_desc'] ?? ($this->getData()['actionCodeDescription'] ?? 'Payment was successful');
    }

    /**
     * Get the order status error message
     * @return string
     * @throws SatimInvalidDataException
     */
    public function getErrorMessage(): string
    {
        if ($this->isRejected()) {
            return '« Votre transaction a été rejetée/ Your transaction was rejected/ تم رفض معاملتك »';
        }

        if ($this->isRefunded()) {
            return 'Payment was refunded';
        }

        return $this->getData()['params']['respCode_desc'] ?? ($this->getData()['actionCodeDescription'] ?? 'Payment failed');
    }

    /**
     * Get the response data from the last request.
     *
     * @return array
     * @throws SatimInvalidDataException
     */

    public function getData(): array
    {
        if (! isset($this->response_data)) {
            throw new SatimInvalidDataException('No data available : call getOrderStatus() or confirmOrder() first.');
        }

        return $this->response_data;
    }

    /**
     * Check if the response data is available before any status checks.
     *
     * @return void
     * @throws SatimInvalidDataException
     */
    protected function ensureDataIsAvailable(): void
    {
        if (! isset($this->response_data)) {
            throw new SatimInvalidDataException('No data available: call confirmOrder() or getOrderStatus() first.');
        }
    }

    /**
     * Check if the transaction was rejected.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isRejected(): bool
    {
        $this->ensureDataIsAvailable();

        return (isset($this->response_data['params']['respCode']) && $this->response_data['params']['respCode'] == '00')
            && $this->response_data['ErrorCode'] == '0'
            && $this->response_data['OrderStatus'] == '3';
    }

    /**
     * Check if the transaction was successful.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isSuccessful(): bool
    {
        $this->ensureDataIsAvailable();

        return isset($this->response_data['OrderStatus'])
            && ($this->response_data['OrderStatus'] == '2' || $this->response_data['OrderStatus'] == '0');
    }

    /**
     * Check if the transaction failed.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isFailed(): bool
    {
        $this->ensureDataIsAvailable();

        return ! $this->isSuccessful();
    }

    /**
     * Check if the transaction was refunded.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isRefunded(): bool
    {
        $this->ensureDataIsAvailable();

        return isset($this->response_data['OrderStatus']) && $this->response_data['OrderStatus'] == '4';
    }

    /**
     * Check if the transaction was cancelled.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isCancelled(): bool
    {
        $this->ensureDataIsAvailable();

        return isset($this->response_data['actionCode']) && $this->response_data['actionCode'] == '10';
    }

    /**
     * Check if the transaction expired.
     *
     * @return bool
     * @throws SatimInvalidDataException
     */
    public function isExpired(): bool
    {
        $this->ensureDataIsAvailable();

        return isset($this->response_data['actionCode']) && $this->response_data['actionCode'] == '-2007';
    }
}
