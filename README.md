# PHP Client for Satim.dz API

![Satim logo](images/satim.png)

<img width="600" src="https://banners.beyondco.de/Satim.dz%20PHP%20Client.png?theme=light&packageManager=composer+require&packageName=piteurstudio%2Fsatim&pattern=architect&style=style_1&description=A+fully+open-source+PHP+package+for+seamless+integration+with+Satim.dz&md=1&showWatermark=1&fontSize=75px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/piteurstudio/php-satim.svg?style=flat-square)](https://packagist.org/packages/piteurstudio/php-satim)
[![Tests](https://img.shields.io/github/actions/workflow/status/piteurstudio/php-satim/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/piteurstudio/php-satim/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/piteurstudio/php-satim.svg?style=flat-square)](https://packagist.org/packages/piteurstudio/php-satim)


A fully open-source PHP package for seamless integration with **Satim.dz**, the official interbank payment gateway in Algeria. 

This package enables merchants and developers to generate secure payment links and retrieve payment statuses directly via the Satim API, facilitating transactions through both **CIB** and **Edahabia** cards.

> ### Requirements
>  PHP 8.1 or higher
> 
>  Satim.dz API credentials (username, password, terminal ID) via [CIBWeb.dz](https://www.cibweb.dz/).

## Installation

You can install the package via composer:

```bash
composer require piteurstudio/satim
```

## Usage

### Configuration

Configure your Satim.dz API credentials using your project's preferred method of storing sensitive information.

Credentials example:
```bash
SATIM_USERNAME=your-satim-username
SATIM_PASSWORD=your-satim-password
SATIM_TERMINAL_ID=your-satim-terminal-id
```

### Initialization

Create a new Satim client by passing your API credentials:

```php
use PiteurStudio\Satim;

$satim = new Satim([
    'username' => env('SATIM_USERNAME'),
    'password' => env('SATIM_PASSWORD'),
    'terminal_id' => env('SATIM_TERMINAL_ID'),
]);
```

### Generate a Payment Link

Create a payment link with a few simple method calls:

```php
$payment = $satim
        ->setAmount(1000) /* Set payment amount in DZD dinars*/
        ->setDescription('Product purchase') /* Optional: Add a description*/
        ->setReturnUrl('https://example.com/success')
        ->setFailUrl('https://example.com/fail') // Optional: Specify a different fail URL
        ->setOrderNumber(1234567890) // Optional: Use custom order number
        ->setTestMode(true) // Optional: Enable test mode
        ->setLanguage('AR') // Optional: Set payment page language (EN, AR, FR - default is FR)
        ->setPaymentTimeout(600) // Optional: Set payment timeout in seconds
        ->setUserDefinedFields([
           'customer_id' => '12345',
           'order_type' => 'premium'
       ]) // Optional: Add custom user-defined fields
        ->generatePayment();

// Retrieve payment information
$paymentDetails = $payment->data();
$orderId = $payment->orderId();
$paymentUrl = $payment->url();

// Redirect user to payment page
$payment->pay();
```
#### Optional configuration methods:


| Method | Parameters | Description | Default Behavior |
|--------|------------|-------------|-----------------|
| `setDescription` | `string $description` | Add a description to the payment | Not set |
| `setFailUrl` | `string $url` | Set a custom fail redirect URL | Uses `setReturnUrl()` |
| `setOrderNumber` | `int $orderNumber` | Use a custom 10-digit order number | Randomly generated |
| `setTestMode` | `bool $isEnabled` | Enable Satim test APIs | Disabled |
| `setLanguage` | `string $language` | Set payment page language | 'FR' (Accepts 'EN', 'AR', 'FR') |
| `setPaymentTimeout` | `int $seconds` | Set payment timeout | 600 seconds (10 minutes) |
| `setUserDefinedFields` | `array $fields` | Add multiple custom user-defined fields | Not set |

- Customize the payment process as needed for your specific use case

### Confirm Payment Status

To check the status of a payment, you can use the `confirmOrder` method with the order ID returned from the payment link generation.

```php
$payment = $satim->confirmOrder($orderId);

if ($payment->isSuccessful()) {

    echo 'Payment was successful : '.$payment->getSuccessMessage();
    
} else {
    // Payment was not successful
    echo 'Payment was not successful' : $payment->getErrorMessage();
    
}
```

### Refund a Payment

To refund a payment, you can use the `refundOrder` method with the order ID returned from the payment link generation.

```php
$refund = $satim->refundOrder($orderId);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contribution

We welcome all contributions! Please follow these guidelines:

1. Document any changes in behavior — ensure `README.md` updated accordingly.
2. Write tests to cover any new functionality.
3. Please ensure that your pull request passes all tests.

## Issues & Suggesting Features

If you encounter any issues or have ideas for new features, please open an issue.

We appreciate your feedback and contributions to help improve this package.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nassim](https://github.com/n4ss1m) / [PiteurStudio](https://github.com/PiteurStudio)
- [All Contributors](../../contributors)

## ⭐ Support Us

If you find this package helpful, please consider giving it a ⭐ on GitHub!
Your support encourages us to keep improving the project.
Thank you!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

## Extra Notes

Satim.dz system operates using the robust banking technologies provided by **BPC Group**.

Note that many functions in BPC Payment System are restricted for public use by Satim.dz

- the official [BPC Payment Documentation](https://dev.bpcbt.com/en/integration/api/rest/rest.html#api_overview).


## Disclaimer

This package is not officially affiliated with or endorsed by **Satim.dz**. The Satim name, logo, and trademarks are the property of **Satim.dz**, Algeria's interbank electronic payment operator.

