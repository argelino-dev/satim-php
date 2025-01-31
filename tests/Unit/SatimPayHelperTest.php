<?php

use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Tests\Helpers\SatimPayHelperTestClass;

beforeEach(function () {
    $this->helper = new SatimPayHelperTestClass();
    $this->helper->setContext(''); // Ensure context is always set
});


it('throws exception when statusOrderResponse is empty', function () {
    $this->helper->setContext('status'); // Ensure context is set to status

    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No payment data found. Call status() first to get the order status and obtain the response data.');

it('throws exception when confirmOrderResponse is empty', function () {
    $this->helper->setContext('confirm'); // Ensure context is set to confirm

    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No payment data found. Call confirm() first to confirm the order and obtain the response data.');


it('throws exception when calling getResponse() without setting context', function () {
    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No response data found. Call one of the methods first to obtain the response data.');

it('throws exception when calling getResponse() without register data', function () {
    $this->helper->setContext('register');
    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No payment data found. Call register() first to register the order and obtain the response data.');

it('retrieves register order response correctly', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['orderId' => '123456']);

    expect($this->helper->getResponse())->toBe(['orderId' => '123456']);
});

it('retrieves confirm order response correctly', function () {
    $this->helper->setContext('confirm');
    $this->helper->setResponse('confirm', ['orderId' => '987654']);

    expect($this->helper->getResponse())->toBe(['orderId' => '987654']);
});

it('retrieves order ID from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['orderId' => '123ABC']);

    expect($this->helper->getOrderId())->toBe('123ABC');
});

it('retrieves IP address from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['Ip' => '192.168.1.1']);

    expect($this->helper->getIpAddress())->toBe('192.168.1.1');
});

it('returns null if IP address is missing', function () {
    $this->helper->setContext('register'); // Ensure context is set
    $this->helper->setResponse('register', ['orderId' => '123456']); // No 'Ip' key

    expect($this->helper->getIpAddress())->toBeNull();
});
it('retrieves cardholder name from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['cardholderName' => 'John Doe']);

    expect($this->helper->getCardHolderName())->toBe('John Doe');
});

it('retrieves card expiry date from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['expiration' => '12/26']);

    expect($this->helper->getCardExpiry())->toBe('12/26');
});

it('retrieves card PAN from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['Pan' => '411111******1111']);

    expect($this->helper->getCardPan())->toBe('411111******1111');
});

it('retrieves approval code from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['approvalCode' => 'A1B2C3']);

    expect($this->helper->getApprovalCode())->toBe('A1B2C3');
});

it('retrieves payment form URL from response', function () {
    $this->helper->setContext('register');
    $this->helper->setResponse('register', ['formUrl' => 'https://satim.dz/payment']);

    expect($this->helper->getUrl())->toBe('https://satim.dz/payment');
});

it('throws exception when payment form URL is missing', function () {
    $this->helper->setContext('register'); // Ensure context is set
    $this->helper->setResponse('register', ['orderId' => '123456']); // No 'formUrl' key

    $this->helper->getUrl();
})->throws(SatimMissingDataException::class, 'No payment form URL found. Call register() first to register the order and obtain the response data.');

it('throws exception when calling getResponse() with an invalid context', function () {
    $this->helper->setContext('invalid');
    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No response data found. Call one of the methods first to obtain the response data.');

it('retrieves status order response correctly', function () {
    $this->helper->setContext('status');
    $this->helper->setResponse('status', ['orderId' => '555666']);

    expect($this->helper->getResponse())->toBe(['orderId' => '555666']);
});

it('retrieves refund order response correctly', function () {
    $this->helper->setContext('refund');
    $this->helper->setResponse('refund', ['orderId' => '888999']);

    expect($this->helper->getResponse())->toBe(['orderId' => '888999']);
});

it('throws exception when calling getResponse() without refund data', function () {
    $this->helper->setContext('refund');
    $this->helper->getResponse();
})->throws(SatimMissingDataException::class, 'No payment data found. Call refund() first to refund the order and obtain the response data.');
