<?php

namespace PiteurStudio;

use JetBrains\PhpStorm\NoReturn;
use PiteurStudio\Exception\SatimApiException;
use PiteurStudio\Exception\SatimInvalidDataException;
use PiteurStudio\Exception\SatimMissingDataException;

trait SatimPayHelper
{

    private ?array $registerPaymentData = null;

    /**
     * Get payment data from generated payment
     * @return array
     * @throws SatimMissingDataException
     */
    public function data(): array
    {
        if(empty($this->registerPaymentData)) {
            throw new SatimMissingDataException('No payment data found. Call generatePayment() first.');
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
    public function orderId(): int
    {
        return $this->data()['orderId'];
    }

    /*
     * Redirect to payment page
     * @return void
     * */
    /**
     * @throws SatimMissingDataException
     */
    #[NoReturn]
    public function pay(): void
    {
        header('Location: '. $this->data()['formUrl']);
        exit;
    }

    /*
     * Get payment url
     * @return string
     * */
    /**
     * @throws SatimMissingDataException
     */
    public function url(): string
    {
        return $this->data()['formUrl'];
    }

    /**
     * Register payment
     * @return static
     * @throws SatimInvalidDataException
     * @throws SatimApiException
     * @throws SatimMissingDataException
     */
    public function generatePayment(): static
    {
        return $this->registerPayment();
    }

}
