<?php

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Exception\SatimUnexpectedValueException;
use PiteurStudio\Tests\Helpers\SatimConfigTestClass;

it('throws exception for missing required data', function () {
    new SatimConfigTestClass([]);
})->throws(SatimMissingDataException::class, 'Missing required data: username, password, terminal_id');

it('throws exception for unexpected keys', function () {
    new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
        'extra' => 'unexpected',
    ]);
})->throws(SatimInvalidArgumentException::class, 'Unexpected keys found: extra');

it('throws exception for empty required fields', function () {
    new SatimConfigTestClass([
        'username' => '',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);
})->throws(SatimUnexpectedValueException::class, 'The value for username cannot be empty.');

it('throws exception for non string fields', function () {
    new SatimConfigTestClass([
        'username' => 123,
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);
})->throws(SatimInvalidArgumentException::class, 'The value for username must be a string.');

it('successfully initializes with valid config', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    expect($config)->toBeInstanceOf(SatimConfigTestClass::class);
});

it('throws exception for negative amount', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->amount(-100);
})->throws(SatimUnexpectedValueException::class, 'Amount must be positive.');

it('sets a valid amount', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->amount(500);

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('amount');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe(500);
});

it('throws exception for description exceeding 598 characters', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $longDescription = str_repeat('A', 599);
    $config->description($longDescription);
})->throws(SatimUnexpectedValueException::class, 'Description must be less than 598 characters.');

it('sets a valid description', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->description('test description');

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('description');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe('test description');
});

it('sets a valid currency', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->currency('USD');

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('currency');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe('840');
});

it('throws exception for invalid currency', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->currency('GBP');
})->throws(SatimUnexpectedValueException::class, 'Invalid currency: Allowed currencies are [DZD, USD, EUR].');

it('throws exception for invalid fail URL', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->failUrl('invalid-url');
})->throws(SatimInvalidArgumentException::class, 'Invalid fail URL.');

it('throws exception for invalid returnUrl', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->returnUrl('invalid-url');
})->throws(SatimInvalidArgumentException::class, 'Invalid return URL. The URL must be a valid URL.');

it('sets a valid fail URL', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->failUrl('https://example.com/fail');

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('failUrl');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe('https://example.com/fail');
});

it('throws exception for invalid order number', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->orderNumber(123);
})->throws(SatimUnexpectedValueException::class, 'Order number must be exactly 10 digits (Satim requirement).');

it('sets a valid order number', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->orderNumber(1234567890);

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('orderNumber');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe(1234567890);
});

it('sets a test_mode ', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->testMode(true);

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('test_mode');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe(true);
});

it('throws exception for invalid language', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->language('DE');
})->throws(SatimUnexpectedValueException::class, 'Language must be FR, AR, or EN.');

it('sets a valid language', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->language('EN');

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('language');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe('EN');
});

it('throws exception for numeric user-defined field key', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->userDefinedField('123', 'value');
})->throws(SatimInvalidArgumentException::class, 'User defined field key must be a string.');

it('sets user-defined fields', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->userDefinedField('key1', 'value1');

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('userDefinedFields');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe(['key1' => 'value1']);
});

it('sets user-defineds fields', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->userDefinedFields([
        'key1' => 'value1',
        'key2' => 'value2',
    ]);

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('userDefinedFields');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe([
        'key1' => 'value1',
        'key2' => 'value2',
    ]);
});

it('throws exception for invalid timeout', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->timeout(500);
})->throws(SatimUnexpectedValueException::class, 'Session timeout must be between 600 and 86400 seconds.');

it('sets a valid timeout', function () {
    $config = new SatimConfigTestClass([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ]);

    $config->timeout(3600);

    $reflector = new ReflectionClass($config);
    $property = $reflector->getProperty('sessionTimeoutSecs');
    $property->setAccessible(true);

    expect($property->getValue($config))->toBe(3600);
});
