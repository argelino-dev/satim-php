<?php

namespace PiteurStudio;

use PiteurStudio\Exception\SatimInvalidDataException;
use PiteurStudio\Exception\SatimMissingDataException;

abstract class SatimConfig
{
    protected bool $debug = true;

    protected string $username;

    protected string $password;

    protected string $terminal_id;

    protected bool $test_mode = false;

    protected string $language = 'FR';

    protected ?int $amount = null;

    protected ?string $failUrl;

    protected ?string $returnUrl = null;

    protected ?string $description = null;

    protected ?int $orderNumber = null;

    protected ?array $userDefinedFields = null;

    protected ?int $sessionTimeoutSecs = null;

    protected string $currency = '012';

    /**
     * Satim constructor.
     *
     * @throws SatimMissingDataException
     */
    protected function initFromArray(array $data): void
    {
        $requiredData = ['username', 'password', 'terminal_id'];

        foreach ($requiredData as $key) {

            if (! isset($data[$key])) {
                throw new SatimMissingDataException('Missing data '.$key.'.');
            }

            if (! is_string($data[$key])) {
                throw new SatimMissingDataException('Data '.$key.' must be a string.');
            }

            if (empty($data[$key])) {
                throw new SatimMissingDataException('Data '.$key.' can not be empty.');
            }

            $this->$key = $data[$key];

        }
    }

    /**
     * Set the amount for the payment.
     */
    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the description for the payment.
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the currency for the payment.
     * Satim planned to support Mastercard and Visa in the future.
     * https://bitakati.dz/fr/actualite/la-satim-certifiee-par-mastercard-et-visa-avant-la-fin-2017-n-3
     *
     * @throws SatimInvalidDataException
     */
    public function setCurrency(string $string): static
    {
        $currencies = [
            'DZD' => '012',
            'USD' => '840',
            'EUR' => '978',
        ];

        if (! array_key_exists($string, $currencies)) {
            throw new SatimInvalidDataException('Invalid currency : Allowed currencies [ DZD , USD , EUR ].');
        }

        $this->currency = $currencies[$string];

        return $this;
    }

    /**
     * Set the fail URL for the payment.
     *
     * @throws SatimInvalidDataException
     */
    public function setFailUrl(string $url): static
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new SatimInvalidDataException('Invalid fail URL.');
        }

        $this->failUrl = $url;

        return $this;
    }

    /**
     * Set the return URL for the payment.
     *
     * @throws SatimInvalidDataException
     */
    public function setReturnUrl(string $url): static
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new SatimInvalidDataException('Invalid return URL.');
        }

        $this->returnUrl = $url;

        return $this;
    }

    /**
     * Set the order number for the payment.
     * The order number must be exactly 10 digits.
     *
     * @throws SatimInvalidDataException
     */
    public function setOrderNumber(int $orderNumber): static
    {

        if (strlen((string) $orderNumber) !== 10) {

            throw new SatimInvalidDataException('Order number must be exactly 10 digits.');
        }

        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Set the test mode for the payment.
     */
    public function setTestMode(bool $isEnabled): static
    {
        $this->test_mode = $isEnabled;

        return $this;
    }

    /**
     * Set the language for the payment.
     * The language must be FR, AR, or EN.
     *
     * @throws SatimInvalidDataException
     */
    public function setLanguage(string $language): static
    {
        $language = strtoupper($language);

        if (! in_array($language, ['FR', 'AR', 'EN'])) {
            throw new SatimInvalidDataException('Language must be FR, AR, or EN.');
        }

        $this->language = $language;

        return $this;
    }

    /**
     * Set the user defined field for the payment.
     *
     * @throws SatimInvalidDataException
     */
    public function setUserDefinedField(string $key, string $value): static
    {

        if (is_numeric($key)) {
            throw new SatimInvalidDataException('User defined field key must be a string.');
        }

        $this->userDefinedFields[$key] = $value;

        return $this;
    }

    /**
     * Set the user defined fields for the payment.
     *
     * @throws SatimInvalidDataException
     */
    public function setUserDefinedFields(array $data): static
    {
        foreach ($data as $variable => $value) {
            $this->setUserDefinedField($variable, $value);
        }

        return $this;
    }

    /**
     * Set the payment timeout for the payment.
     */
    public function setPaymentTimeout(int $seconds): static
    {
        $this->sessionTimeoutSecs = $seconds;

        return $this;
    }
}
