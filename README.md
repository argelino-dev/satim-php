# PHP Client for Satim.dz API

![Satim logo](images/satim.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/piteurstudio/php-satim.svg?style=flat-square)](https://packagist.org/packages/piteurstudio/php-satim)
[![Tests](https://img.shields.io/github/actions/workflow/status/piteurstudio/php-satim/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/piteurstudio/php-satim/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/piteurstudio/php-satim.svg?style=flat-square)](https://packagist.org/packages/piteurstudio/php-satim)


A fully open-source PHP package for seamless integration with **Satim.dz**, the official interbank payment gateway in Algeria. 

This package enables merchants and developers to generate secure payment links and retrieve payment statuses directly via the Satim API, facilitating transactions through both **CIB** and **Edahabia** cards.


## Features
- Generate payment links using the Satim.dz API
- Retrieve and validate payment statuses
- Supports test and production environments
- Fluent API for easy integration
- Built-in error handling for invalid credentials and other common issues

## About Satim.dz
**Satim.dz** is the leading interbank electronic payment operator in Algeria, enabling online payments through both **CIB** and **Edahabia** cards. 

The payment system operates using the robust banking technologies provided by **BPC Group**.

To learn more about Satim's API, visit the official [BPC Payment Documentation](https://dev.bpcbt.com/en/integration/api/rest/rest.html#api_overview). 

Note that many functions are restricted for public use.

### How to Get Access
To start using the Satim.dz API, you’ll need to create an account via the [CIB Web Portal](https://www.cibweb.dz/). 

Once registered, you will receive your API credentials and a list of the permitted functions available for your integration.

## Requirements

- PHP 8.1 or higher
- Satim.dz API credentials (username, password, terminal ID)

## Installation

You can install the package via composer:

```bash
composer require piteurstudio/satim
```

## Usage

### Generate a Payment Link

To generate a payment link, you'll need to create a new instance of the `Satim` class and provide your Satim.dz API credentials.

```php
    
use PiteurStudio\Satim;

$satim = new Satim([
    'username' => env('SATIM_USERNAME'),
    'password' => env('SATIM_PASSWORD'),
    'terminal_id' => env('SATIM_TERMINAL_ID'),
]);

$payment = $satim
        ->setAmount(1000)
        ->setReturnUrl('https://example.com/success')
        ->generatePayment();

// Store this ID to check the payment status later
$orderId = $payment['orderId']; 

// Redirect your user to this URL to complete the payment
$formUrl = $payment['formUrl'];
```

### Confirm Payment Status

To check the status of a payment, you can use the `confirmOrder` method with the order ID returned from the payment link generation.

```php
    
use PiteurStudio\Satim;

/**
 * Create a new instance of the Satim class with API credentials.
 *
 * @param array $credentials An array containing 'username', 'password', and 'terminal_id'.
 */

$satim = new Satim([
    'username' => env('SATIM_USERNAME'),
    'password' => env('SATIM_PASSWORD'),
    'terminal_id' => env('SATIM_TERMINAL_ID'),
]);


$payment = $satim->confirmOrder($orderId);

if ($payment->isSuccessful()) {

    echo 'Payment was successful : '.$payment->getSuccessMessage();
    
} else {
    // Payment was not successful
    echo 'Payment was not successful' : $payment->getErrorMessage();
    
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Feel free to submit a pull request or open an issue on GitHub. 

Together, we can improve and expand this package to better serve the Algerian developer community.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nassim](https://github.com/n4ss1m) / [PiteurStudio](https://github.com/PiteurStudio)
- [All Contributors](../../contributors)

## Support Us

If you find this package helpful and would like to support its development, please consider buying me a coffee. 

Your support is greatly appreciated!


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer
This package is not officially affiliated with or endorsed by **Satim.dz**. The Satim name, logo, and trademarks are the property of **Satim.dz**, Algeria's interbank electronic payment operator.

