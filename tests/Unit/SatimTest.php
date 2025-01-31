<?php

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Exception\SatimUnexpectedResponseException;
use PiteurStudio\Satim;
use PiteurStudio\Tests\Helpers\MockHttpClientService;

beforeEach(function () {
    $this->mockHttpClient = new MockHttpClientService();

    $this->satim = new Satim([
        'username' => 'test_user',
        'password' => 'test_pass',
        'terminal_id' => '123456',
    ], $this->mockHttpClient);
});

it('throws exception when returnUrl is missing', function () {
    $this->satim->register();
})->throws(SatimMissingDataException::class, 'Return URL missing. Call returnUrl() to set it.');

it('throws exception when amount is missing', function () {
    $this->satim->returnUrl('https://example.com/return');
    $this->satim->register();
})->throws(SatimMissingDataException::class, 'Amount missing. Call the amount() method to set it.');

it('registers a payment successfully', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $property = $reflector->getProperty('registerOrderResponse');
    $property->setAccessible(true);

    expect($property->getValue($this->satim))->toBe(['errorCode' => '0', 'orderId' => '1234567890']);
});

it('throws exception if register API returns an error', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '1001',
        'errorMessage' => 'Invalid credentials',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->register();
})->throws(SatimUnexpectedResponseException::class, 'registerPayment Error {errorCode: 1001 , errorMessage: Invalid credentials}');

it('confirms a payment successfully', function () {
    $this->mockHttpClient->addMockResponse('/confirmOrder.do', [
        'status' => 'confirmed',
    ]);

    $this->satim->confirm('1234567890');

    $reflector = new ReflectionClass($this->satim);
    $property = $reflector->getProperty('confirmOrderResponse');
    $property->setAccessible(true);

    expect($property->getValue($this->satim))->toBe(['status' => 'confirmed']);
});

it('throws exception when confirming with an empty order ID', function () {
    $this->satim->confirm('');
})->throws(SatimInvalidArgumentException::class, 'Order ID is required for confirmation');

it('retrieves order status successfully', function () {
    $this->mockHttpClient->addMockResponse('/getOrderStatus.do', [
        'status' => 'paid',
    ]);

    $this->satim->status('1234567890');

    $reflector = new ReflectionClass($this->satim);
    $property = $reflector->getProperty('statusOrderResponse');
    $property->setAccessible(true);

    expect($property->getValue($this->satim))->toBe(['status' => 'paid']);
});

it('throws exception when retrieving status with an empty order ID', function () {
    $this->satim->status('');
})->throws(SatimInvalidArgumentException::class, 'Order ID is required for confirmation');

it('processes a refund successfully', function () {
    $this->mockHttpClient->addMockResponse('/refund.do', [
        'refundStatus' => 'success',
    ]);

    $response = $this->satim->refund('1234567890', 500);
    expect($response)->toBe(['refundStatus' => 'success']);
});

it('throws exception when refunding with an empty order ID', function () {
    $this->satim->refund('', 500);
})->throws(SatimInvalidArgumentException::class, 'Order ID is required for refund');

it('throws exception when refunding with an invalid amount', function () {
    $this->satim->refund('1234567890', -500);
})->throws(SatimInvalidArgumentException::class, 'Amount must be a positive integer');

it('adds user-defined fields to request data', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->userDefinedFields([
        'custom_field_1' => 'value1',
        'custom_field_2' => 'value2',
    ]);

    // Trigger `register()` which calls `buildData()`
    $this->satim->register();

    // Use reflection to access private `buildData()` method
    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    // Decode JSON `jsonParams`
    $jsonParams = json_decode($requestData['jsonParams'], true);

    expect($jsonParams)->toHaveKeys(['force_terminal_id', 'custom_field_1', 'custom_field_2'])
        ->and($jsonParams['force_terminal_id'])->toBe('123456')
        ->and($jsonParams['custom_field_1'])->toBe('value1')
        ->and($jsonParams['custom_field_2'])->toBe('value2');
});

it('does not add user-defined fields if they are empty', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);

    // Ensure no user-defined fields are set
    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    // Decode JSON `jsonParams`
    $jsonParams = json_decode($requestData['jsonParams'], true);

    expect($jsonParams)->toHaveKey('force_terminal_id')
        ->and($jsonParams)->not()->toHaveKeys(['custom_field_1', 'custom_field_2']);
});

it('overrides existing keys if user-defined fields contain force_terminal_id', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->userDefinedFields([
        'force_terminal_id' => 'OVERWRITTEN_VALUE',
        'custom_field' => 'some_value',
    ]);

    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    // Decode JSON `jsonParams`
    $jsonParams = json_decode($requestData['jsonParams'], true);

    expect($jsonParams)->toHaveKey('force_terminal_id')
        ->and($jsonParams['force_terminal_id'])->toBe('OVERWRITTEN_VALUE')
        ->and($jsonParams['custom_field'])->toBe('some_value');
});

it('adds description to request data when set', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->description('Test payment description');

    // Trigger `register()` which calls `buildData()`
    $this->satim->register();

    // Use reflection to access private `buildData()` method
    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    expect($requestData)->toHaveKey('description')
        ->and($requestData['description'])->toBe('Test payment description');
});

it('does not add description if not set', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);

    // Ensure no description is set
    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    expect($requestData)->not()->toHaveKey('description');
});

it('adds session timeout to request data when set', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);
    $this->satim->timeout(3600);

    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    expect($requestData)->toHaveKey('sessionTimeoutSecs')
        ->and($requestData['sessionTimeoutSecs'])->toBe(3600);
});

it('does not add session timeout if not set', function () {
    $this->mockHttpClient->addMockResponse('/register.do', [
        'errorCode' => '0',
        'orderId' => '1234567890',
    ]);

    $this->satim->returnUrl('https://example.com/return');
    $this->satim->amount(1000);

    // Ensure no session timeout is set
    $this->satim->register();

    $reflector = new ReflectionClass($this->satim);
    $method = $reflector->getMethod('buildData');
    $method->setAccessible(true);

    $requestData = $method->invoke($this->satim);

    expect($requestData)->not()->toHaveKey('sessionTimeoutSecs');
});
