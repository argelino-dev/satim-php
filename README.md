<p align="center"><img src="images/satim.png" alt="Satim logo"></p>

<p align="center">
<img width="800" src="https://banners.beyondco.de/Satim.dz%20PHP%20Client.png?theme=light&packageManager=composer+require&packageName=piteurstudio%2Fsatim-php&pattern=architect&style=style_1&description=A+fully+open-source+PHP+package+for+seamless+integration+with+Satim.dz&md=1&showWatermark=1&fontSize=75px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg" alt="Satim PHP Client">
</p>

<p align="center">
<a href="https://packagist.org/packages/piteurstudio/satim-php"><img src="http://poser.pugx.org/piteurstudio/satim-php/require/php" alt="PHP Version Require"></a>
<a href="https://packagist.org/packages/piteurstudio/satim-php"><img src="https://img.shields.io/packagist/v/piteurstudio/satim-php.svg?style=flat" alt="Latest Version on Packagist"></a>
<a href="https://codecov.io/gh/PiteurStudio/satim-php"><img src="https://codecov.io/gh/PiteurStudio/satim-php/branch/main/graph/badge.svg?token=MXKQCQ4AGX" alt="codecov"></a>
<a href="https://github.com/PiteurStudio/satim-php/blob/main/phpstan.neon"><img src="https://img.shields.io/badge/PHPStan-max-blue.svg?style=flat" alt="phpstan"></a>
<a href="https://github.com/piteurstudio/php-satim/actions/workflows/run-tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/piteurstudio/php-satim/run-tests.yml?branch=main&amp;label=tests&amp;style=flat" alt="Tests"></a>
<a href="https://packagist.org/packages/piteurstudio/satim-php"><img src="https://img.shields.io/packagist/dt/piteurstudio/satim-php.svg?style=flat" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/piteurstudio/satim-php"><img src="http://poser.pugx.org/piteurstudio/satim-php/license" alt="License"></a>
</p>

# Satim.dz PHP Client

A fully open-source PHP package for seamless integration with **Satim.dz**, the official interbank payment gateway in Algeria. 

This package enables merchants and developers to generate secure payment links and retrieve payment statuses directly via the Satim API, facilitating transactions through both **CIB** and **Edahabia** cards.

> ### Requirements
>  PHP 8.1 or higher
> 
>  Satim.dz API credentials (username, password, terminal ID) via [CIBWeb.dz](https://www.cibweb.dz/).

## Installation

You can install the package via composer:

```bash
composer require piteurstudio/satim-php
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

### Main Methods

The Satim provides the following methods:

- Register a new payment order: `register()`
- Confirm the status of a payment: `confirm($orderId)`
- Refund a payment: `refund($orderId)`
- Retrieve payment status: `status($orderId)`

### Generate a Payment Link

Create a payment link with a few simple method calls:

```php
$payment = $satim
        ->amount(1000) /* Set payment amount in DZD dinars*/
        ->description('Product purchase') /* Optional: Add a description*/
        ->returnUrl('https://example.com/success')
        ->failUrl('https://example.com/fail') // Optional: Specify a different fail URL
        ->orderNumber(1234567890) // Optional: Use custom order number
        ->testMode(true) // Optional: Enable test mode
        ->language('AR') // Optional: Set payment page language (EN, AR, FR - default is FR)
        ->timeout(600) // Optional: Set payment timeout in seconds
        ->userDefinedFields([
           'customer_id' => '12345',
           'order_type' => 'premium'
       ]) // Optional: Add custom user-defined fields
        ->register();

// Retrieve payment information
$paymentDetails = $payment->getResponse();
$orderId = $payment->getOrderId();
$paymentUrl = $payment->getUrl();

// Redirect user to payment page
$payment->redirect();
```
#### Optional configuration methods:


| Method              | Parameters            | Description                             | Default Behavior                |
|---------------------|-----------------------|-----------------------------------------|---------------------------------|
| `description`       | `string $description` | Add a description to the payment        | Not set                         |
| `failUrl`           | `string $url`         | Set a custom fail redirect URL          | Uses `returnUrl()`              |
| `orderNumber`       | `int $orderNumber`    | Use a custom 10-digit order number      | Randomly generated              |
| `testMode`          | `bool $isEnabled`     | Enable Satim test APIs                  | Disabled                        |
| `language`          | `string $language`    | Set payment page language               | 'FR' (Accepts 'EN', 'AR', 'FR') |
| `timeout`           | `int $seconds`        | Set payment timeout                     | 600 seconds (10 minutes)        |
| `userDefinedFields` | `array $fields`       | Add multiple custom user-defined fields | Not set                         |

- Customize the payment process as needed for your specific use case

### Confirm Payment

This method need to be used when the user is redirected back to your website after the payment process on return URL or fail URL.

To confirm the status of a payment, you can use the `confirm` method with the order ID returned from the payment link generation.

```php
$orderConfirmation = $satim->confirm($orderId);

// Retrieve payment status
$orderConfirmation->getResponse();

if ($orderConfirmation->isSuccessful()) {

    echo 'Payment was successful : '.$orderConfirmation->getSuccessMessage();
    
} else {
    // Payment was not successful
    echo 'Payment was not successful' : $orderConfirmation->getErrorMessage();
    
}
```

### Refund a Payment

To refund a payment, you can use the `refundOrder` method with the order ID returned from the payment link generation.

```php
$refundOrder = $satim->refund($orderId);

$refundOrder->getResponse();
```

### Payment Status

To retrieve the payment status, you can use the `status` method with the order ID returned from the payment link generation.

```php
$orderStatus = $satim->status($orderId);

$orderStatus->getResponse();
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

---

## Extra Notes

Satim.dz system operates using the robust banking technologies provided by **BPC Group**.

Note that many functions in BPC Payment System are restricted for public use by Satim.dz

- the official [BPC Payment Documentation](https://dev.bpcbt.com/en/integration/api/rest/rest.html#api_overview).

### Similar Packages

The following packages provide similar functionality by interacting with Satim through third-party services, rather than directly integrating with Satim:

- **[Chargily](https://github.com/orgs/Chargily/repositories?q=php)**
- **[Slick Pay](https://github.com/orgs/Slick-Pay-Algeria/repositories)**
- **[ALPAY](https://github.com/alpaydz?tab=repositories)**

These packages may be useful depending on your use case. If you’ve developed a package that interacts with Satim via a third-party, feel free to submit a pull request to add it here.

---

## Credits

- [Nassim](https://github.com/n4ss1m) / [PiteurStudio](https://github.com/PiteurStudio)
- [All Contributors](../../contributors)

## ⭐ Support Us

If you find this package helpful, please consider giving it a ⭐ on GitHub!
Your support encourages us to keep improving the project.
Thank you!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer

This package is not officially affiliated with or endorsed by **Satim.dz** or any other third-party. The name, logo, and trademarks are the property of their respective owners.
