<?php

declare(strict_types=1);

namespace PiteurStudio;

use PiteurStudio\Client\HttpClientService;
use PiteurStudio\Exception\SatimApiException;
use PiteurStudio\Exception\SatimInvalidDataException;
use PiteurStudio\Exception\SatimMissingDataException;

class Satim extends SatimConfig
{
    use SatimStatusChecker;
    use SatimPayHelper;

    protected HttpClientService $httpClientService;

    /**
     * @throws SatimMissingDataException
     */
    public function __construct(array $data, ?HttpClientService $httpClientService = null)
    {
        $this->httpClientService = $httpClientService ?? new HttpClientService($this->test_mode); // Automatically create HttpClientService if not provided
        $this->initFromArray($data);
    }

    /**
     * Validate payment data before making a request.
     *
     * @throws SatimMissingDataException
     * @throws SatimInvalidDataException
     */
    private function validatePaymentData(): void
    {

        if (! $this->returnUrl) {
            throw new SatimMissingDataException('Return URL missing. Call setReturnUrl().');
        }

        if (! $this->orderNumber) {

            $this->setOrderNumber(mt_rand(1000000000, 9999999999));

        }

        if (strlen((string) $this->orderNumber) !== 10) {

            throw new SatimInvalidDataException('Order number must be exactly 10 digits ( Satim requirement ) .');
        }

        if (! $this->amount) {
            throw new SatimMissingDataException('Amount missing. Call setAmount().');
        }

        if ($this->amount < 0) {
            throw new SatimInvalidDataException('Amount must be positive.');
        }

        if ($this->description) {

            if (strlen($this->description) > 598) {
                throw new SatimInvalidDataException('Description must be less than 598 characters.');
            }

        }

        if ($this->sessionTimeoutSecs) {

            if ($this->sessionTimeoutSecs < 600 || $this->sessionTimeoutSecs > 86400) {
                throw new SatimInvalidDataException('Session timeout must be between 600 and 86400 seconds.');
            }

        }

    }

    /**
     * Build the request data for payment registration.
     */
    private function buildPaymentData(): array
    {

        $additionalData = [
            'force_terminal_id' => $this->terminal_id,
        ];

        if ($this->userDefinedFields) {
            $additionalData = array_merge($additionalData, $this->userDefinedFields);
        }

        $data = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderNumber' => $this->orderNumber,
            'amount' => $this->amount * 100, // convert to minor units
            'currency' => $this->currency,
            'returnUrl' => $this->returnUrl,
            'failUrl' => $this->failUrl ?? $this->returnUrl,
            'language' => $this->language,
            'jsonParams' => json_encode($additionalData),
        ];

        if ($this->description) {
            $data['description'] = $this->description;
        }

        if ($this->sessionTimeoutSecs) {
            $data['sessionTimeoutSecs'] = $this->sessionTimeoutSecs;
        }

        return $data;

    }

    /**
     * Register a payment with Satim API.
     *
     * @throws SatimInvalidDataException
     * @throws SatimMissingDataException
     * @throws SatimApiException
     */
    public function registerPayment(): static
    {

        // Perform validation
        $this->validatePaymentData();

        // Build request data
        $data = $this->buildPaymentData();

        $result = $this->httpClientService->handleApiRequest('/register.do', $data);

        if ($result['errorCode'] !== '0') {

            $errorMessage = $result['errorMessage'] ?? 'Unknown error';

            throw new SatimInvalidDataException('registerPayment Error {errorCode: '.$result['errorCode'].' , errorMessage: '.$errorMessage.'}');
        }

        $this->registerPaymentData = $result;

        return $this;

    }

    /**
     * Confirm the payment with Satim API.
     *
     * @throws SatimApiException
     */
    public function confirmPayment(string $orderId): static
    {
        $data = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId' => $orderId,
            'language' => $this->language,
        ];

        $this->confirmPaymentData = $this->httpClientService->handleApiRequest('/confirmOrder.do', $data);

        return $this;
    }

    /**
     * Get the status of a payment from Satim API.
     *
     * @throws SatimApiException
     */
    public function checkPaymentStatus(string $orderId): static
    {
        $data = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId' => $orderId,
            'language' => $this->language,
        ];

        $this->confirmPaymentData = $this->httpClientService->handleApiRequest('/getOrderStatus.do', $data);

        return $this;
    }

    /**
     * Refund a payment with Satim API.
     *
     * @throws SatimApiException
     */
    public function refundPayment(string $orderId, int $amount): array
    {
        $data = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId' => $orderId,
            'amount' => $amount * 100,
            'language' => $this->language,
        ];

        return $this->httpClientService->handleApiRequest('/refund.do', $data);
    }
}
