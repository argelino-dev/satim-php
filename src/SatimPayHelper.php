<?php

namespace PiteurStudio;

use JetBrains\PhpStorm\NoReturn;
use PiteurStudio\Exception\SatimMissingDataException;

trait SatimPayHelper
{
    private ?array $registerPaymentData = null;

    /**
     * Get payment data from generated payment
     *
     * @throws SatimMissingDataException
     */
    public function getResponse(): array
    {
        if (empty($this->registerPaymentData)) {
            throw new SatimMissingDataException('No payment data found. Call registerOrder() first.');
        }

        return $this->registerPaymentData;
    }

    /*
     * Get order id from generated payment
     * @return int
     * */
    /**
     * @throws SatimMissingDataException
     */
    public function getOrderId(): int
    {
        return $this->getResponse()['orderId'];
    }

    /*
     * Redirect to payment page
     * @return void
     * */
    /**
     * @throws SatimMissingDataException
     */
    #[NoReturn]
    public function redirect(): void
    {
        header('Location: '.$this->getResponse()['formUrl']);
        exit;
    }

    /*
     * Get payment url
     * @return string
     * */
    /**
     * @throws SatimMissingDataException
     */
    public function getUrl(): string
    {
        return $this->getResponse()['formUrl'];
    }
}
