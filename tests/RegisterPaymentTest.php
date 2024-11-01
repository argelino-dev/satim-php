<?php

use PiteurStudio\Exception\SatimInvalidDataException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Satim;

it('can not register payment without returnUrl', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->registerPayment();

})->throws(SatimMissingDataException::class);

it('can not register payment without a valid returnUrl', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->setReturnUrl('invalid_url')
        ->registerPayment();

})->throws(SatimInvalidDataException::class);

it('can not register payment without amount', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->setReturnUrl('https://your-return-url.com/payments')
        ->registerPayment();

})->throws(SatimMissingDataException::class);

it('can not register payment without valid positive amount', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->setReturnUrl('https://your-return-url.com/payments')
        ->setAmount(-5000)
        ->registerPayment();

})->throws(SatimInvalidDataException::class);
