<?php

namespace PiteurStudio;

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Exception\SatimUnexpectedValueException;

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

    /**
     * The user defined fields for the payment.
     *
     * @var array<non-empty-string, non-empty-string>|null
     */
    protected ?array $userDefinedFields = null;

    protected ?int $sessionTimeoutSecs = null;

    protected string $currency = '012';

    /**
     * The list of supported currencies.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    protected array $currencies = [
        'DZD' => '012',
        'USD' => '840',
        'EUR' => '978',
    ];

    /**
     * Satim constructor.
     *
     * @param  array{username: non-empty-string, password: non-empty-string, terminal_id: non-empty-string}  $data  The configuration data for the Satim object.
     *
     * @throws SatimMissingDataException|SatimInvalidArgumentException|SatimUnexpectedValueException
     */
    protected function initFromArray(array $data): void
    {
        $requiredData = ['username', 'password', 'terminal_id'];

        // First validate that we have only the expected keys
        $unexpectedKeys = array_diff(array_keys($data), $requiredData);
        if (! empty($unexpectedKeys)) {
            throw new SatimInvalidArgumentException('Unexpected keys found: '.implode(', ', $unexpectedKeys));
        }

        // Then validate that all required keys exist
        $missingKeys = array_diff($requiredData, array_keys($data));
        if (! empty($missingKeys)) {
            throw new SatimMissingDataException('Missing required data: '.implode(', ', $missingKeys));
        }

        // Now validate each value
        foreach ($requiredData as $key) {
            if (! is_string($data[$key])) {
                throw new SatimInvalidArgumentException("The value for {$key} must be a string.");
            }

            if (empty($data[$key])) {
                throw new SatimUnexpectedValueException("The value for {$key} cannot be empty.");
            }

            $this->$key = $data[$key];
        }
    }

    /**
     * Set the amount for the payment.
     *
     * The amount must be a positive integer.
     *
     * @param  int  $amount  The amount of the payment in cents.
     *
     * @throws SatimUnexpectedValueException If the amount is negative.
     */
    public function amount(int $amount): static
    {

        if ($amount < 0) {
            throw new SatimUnexpectedValueException('Amount must be positive.');
        }

        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the description for the payment.
     *
     * The description must be less than 598 characters.
     *
     * @param  string  $description  The description of the payment.
     *
     * @throws SatimUnexpectedValueException If the description is too long.
     */
    public function description(string $description): static
    {
        // The description must be less than 598 characters.
        if (strlen($description) > 598) {
            throw new SatimUnexpectedValueException('Description must be less than 598 characters.');
        }

        $this->description = $description;

        return $this;
    }

    /**
     * Set the currency for the payment.
     * Satim planned to support Mastercard and Visa in the future.
     * https://bitakati.dz/fr/actualite/la-satim-certifiee-par-mastercard-et-visa-avant-la-fin-2017-n-3
     *
     * @param  string  $currency  The currency code (e.g., 'DZD', 'USD', 'EUR').
     *
     * @throws SatimUnexpectedValueException If the currency is invalid.
     */
    public function currency(string $currency): static
    {
        // Check if the provided currency exists in the allowed currencies list
        if (! array_key_exists($currency, $this->currencies)) {
            throw new SatimUnexpectedValueException('Invalid currency: Allowed currencies are [DZD, USD, EUR].');
        }

        // Set the currency for the payment
        $this->currency = $this->currencies[$currency];

        return $this;
    }

    /**
     * Set the fail URL for the payment.
     *
     * The fail URL is the URL that the client will be redirected to
     * if the payment fails.
     *
     * @param  string  $url  The URL to redirect to if the payment fails.
     *
     * @throws SatimInvalidArgumentException If the URL is invalid.
     */
    public function failUrl(string $url): static
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new SatimInvalidArgumentException('Invalid fail URL.');
        }

        $this->failUrl = $url;

        return $this;
    }

    /**
     * Set the return URL for the payment.
     *
     * The return URL is the URL that the client will be redirected to
     * after the payment is processed.
     *
     * @param  string  $url  The URL to redirect to after the payment is processed.
     *
     * @throws SatimInvalidArgumentException If the URL is invalid.
     */
    public function returnUrl(string $url): static
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new SatimInvalidArgumentException('Invalid return URL. The URL must be a valid URL.');
        }

        $this->returnUrl = $url;

        return $this;
    }

    /**
     * Set the order number for the payment.
     * The order number must be exactly 10 digits (Satim requirement).
     * You can use a random number or a unique identifier from your database.
     *
     * @param  int  $orderNumber  The order number for the payment.
     *
     * @throws SatimUnexpectedValueException If the order number is not exactly 10 digits.
     */
    public function orderNumber(int $orderNumber): static
    {

        if (strlen((string) $orderNumber) !== 10) {

            throw new SatimUnexpectedValueException('Order number must be exactly 10 digits (Satim requirement).');
        }

        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Set the test mode for the payment.
     *
     * @param  bool  $isEnabled  Whether to enable test mode.
     */
    public function testMode(bool $isEnabled): static
    {
        $this->test_mode = $isEnabled;

        return $this;
    }

    /**
     * Set the language for the payment.
     *
     * The language must be one of the following:
     *
     *  - FR (French)
     *  - AR (Arabic)
     *  - EN (English)
     *
     * @param  string  $language  The language to use for the payment.
     *
     * @throws SatimUnexpectedValueException If the language is invalid.
     */
    public function language(string $language): static
    {
        $language = strtoupper($language);

        if (! in_array($language, ['FR', 'AR', 'EN'])) {
            throw new SatimUnexpectedValueException('Language must be FR, AR, or EN.');
        }

        $this->language = $language;

        return $this;
    }

    /**
     * Set the user defined field for the payment.
     *
     * This method allows you to set a user defined field for the payment.
     * The key must be a string and the value must be a string.
     *
     * @param  non-empty-string  $key  The key of the user defined field.
     * @param  non-empty-string  $value  The value of the user defined field.
     *
     * @throws SatimInvalidArgumentException If the key is a numeric string.
     */
    public function userDefinedField(string $key, string $value): static
    {

        if (is_numeric($key)) {
            throw new SatimInvalidArgumentException('User defined field key must be a string.');
        }

        $this->userDefinedFields[$key] = $value;

        return $this;
    }

    /**
     * Set the user defined fields for the payment.
     *
     * @param  array<non-empty-string,non-empty-string>  $data  The user defined fields to set.
     *
     * @throws SatimInvalidArgumentException If any of the keys in the $data array are numeric strings.
     */
    public function userDefinedFields(array $data): static
    {
        foreach ($data as $variable => $value) {
            $this->userDefinedField($variable, $value);
        }

        return $this;
    }

    /**
     * Set the payment session timeout.
     *
     * This method sets the session timeout for the payment process.
     * The timeout must be between 600 seconds (10 minutes) and 86400 seconds (24 hours).
     *
     * @param  int  $seconds  The session timeout in seconds.
     *
     * @throws SatimUnexpectedValueException If the timeout is not within the allowed range.
     */
    public function timeout(int $seconds): static
    {
        // Validate that the timeout is within the allowed range
        if ($seconds < 600 || $seconds > 86400) {
            throw new SatimUnexpectedValueException('Session timeout must be between 600 and 86400 seconds.');
        }

        // Set the session timeout
        $this->sessionTimeoutSecs = $seconds;

        return $this;
    }
}
