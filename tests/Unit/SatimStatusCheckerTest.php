<?php

use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Tests\Helpers\SatimStatusCheckerTestClass;

beforeEach(function (): void {
    $this->helper = new SatimStatusCheckerTestClass;
});

// ✅ Test: getSuccessMessage()
it('returns success message when transaction is successful', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2', 'params' => ['respCode_desc' => 'Success']]);

    expect($this->helper->getSuccessMessage())->toBe('Success');
});

it('returns default success message if success response is missing', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->getSuccessMessage())->toBe('Payment was successful');
});

// ✅ Test: getErrorMessage()
it('returns rejection message when transaction is rejected', function (): void {
    $this->helper->setResponse(['OrderStatus' => '3']);

    expect($this->helper->getErrorMessage())->toBe('« Votre transaction a été rejetée/ Your transaction was rejected/ تم رفض معاملتك »');
});

it('returns refund message when transaction is refunded', function (): void {
    $this->helper->setResponse(['OrderStatus' => '4']);

    expect($this->helper->getErrorMessage())->toBe('Payment was refunded');
});

it('returns default error message if error response is missing', function (): void {
    $this->helper->setResponse([]);

    expect($this->helper->getErrorMessage())->toBe('Payment failed');
});

// ✅ Test: isRejected()
it('returns true if transaction is rejected', function (): void {
    $this->helper->setResponse(['OrderStatus' => '3', 'ErrorMessage' => 'Payment is declined']);

    expect($this->helper->isRejected())->toBeTrue();
});

it('returns false if transaction is not rejected', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->isRejected())->toBeFalse();
});

// ✅ Test: isSuccessful()
it('returns true if transaction is successful', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->isSuccessful())->toBeTrue();
});

it('returns false if transaction is not successful', function (): void {
    $this->helper->setResponse(['OrderStatus' => '3']);

    expect($this->helper->isSuccessful())->toBeFalse();
});

// ✅ Test: isFailed()
it('returns true if transaction failed', function (): void {
    $this->helper->setResponse(['OrderStatus' => '3']);

    expect($this->helper->isFailed())->toBeTrue();
});

it('returns false if transaction did not fail', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->isFailed())->toBeFalse();
});

// ✅ Test: isRefunded()
it('returns true if transaction is refunded', function (): void {
    $this->helper->setResponse(['OrderStatus' => '4']);

    expect($this->helper->isRefunded())->toBeTrue();
});

it('returns false if transaction is not refunded', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->isRefunded())->toBeFalse();
});

// ✅ Test: isCancelled()
it('returns true if transaction is cancelled', function (): void {
    $this->helper->setResponse(['actionCode' => '10']);

    expect($this->helper->isCancelled())->toBeTrue();
});

it('returns false if transaction is not cancelled', function (): void {
    $this->helper->setResponse(['actionCode' => '0']);

    expect($this->helper->isCancelled())->toBeFalse();
});

it('returns true if cancellation message is in ErrorMessage', function (): void {
    $this->helper->setResponse(['ErrorMessage' => 'Payment is cancelled due to customer request']);

    expect($this->helper->isCancelled())->toBeTrue();
});

// ✅ Test: isExpired()
it('returns true if transaction expired', function (): void {
    $this->helper->setResponse(['actionCode' => '-2007']);

    expect($this->helper->isExpired())->toBeTrue();
});

it('returns false if transaction is not expired', function (): void {
    $this->helper->setResponse(['actionCode' => '0']);

    expect($this->helper->isExpired())->toBeFalse();
});

// ✅ Test: Exception Handling
it('throws exception when getSuccessMessage() is called without response data', function (): void {
    $this->helper->getSuccessMessage();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when getErrorMessage() is called without response data', function (): void {
    $this->helper->getErrorMessage();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isRejected() is called without response data', function (): void {
    $this->helper->isRejected();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isSuccessful() is called without response data', function (): void {
    $this->helper->isSuccessful();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isFailed() is called without response data', function (): void {
    $this->helper->isFailed();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isRefunded() is called without response data', function (): void {
    $this->helper->isRefunded();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isCancelled() is called without response data', function (): void {
    $this->helper->isCancelled();
})->throws(SatimMissingDataException::class, 'No response data found.');

it('throws exception when isExpired() is called without response data', function (): void {
    $this->helper->isExpired();
})->throws(SatimMissingDataException::class, 'No response data found.');

// ✅ Test: If transaction is NOT successful, getSuccessMessage() should return getErrorMessage()
it('returns error message when transaction is not successful', function (): void {
    $this->helper->setResponse(['OrderStatus' => '3']); // Not successful (should be 2 or 0)

    expect($this->helper->getSuccessMessage())->toBe($this->helper->getErrorMessage());
});

// ✅ Test: If success message is missing, return default success message
it('returns default success message when response is missing', function (): void {
    $this->helper->setResponse(['OrderStatus' => '2']);

    expect($this->helper->getSuccessMessage())->toBe('Payment was successful');
});

it('isCancelled returns false if errorCode is 0', function (): void {
    $this->helper->setResponse(['errorCode' => '0']);

    expect($this->helper->isCancelled())->toBeFalse();
});

it('returns false if errorCode is 0', function (): void {
    $this->helper->setResponse(['errorCode' => '0']);

    expect($this->helper->isRejected())->toBeFalse();
});

it('returns true if actionCode is 2003', function (): void {
    $this->helper->setResponse(['actionCode' => '2003']);

    expect($this->helper->isRejected())->toBeTrue();
});
