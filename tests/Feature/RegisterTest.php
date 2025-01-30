<?php

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Exception\SatimUnexpectedValueException;
use PiteurStudio\Satim;

it('can not register payment without returnUrl', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->register();

})->throws(SatimMissingDataException::class);

it('can not register payment without a valid returnUrl', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->returnUrl('invalid_url');

})->throws(SatimInvalidArgumentException::class);

it('can not register payment without amount', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->returnUrl('https://your-return-url.com/payments')
        ->register();

})->throws(SatimMissingDataException::class);

it('can not register payment without valid positive amount', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim
        ->returnUrl('https://your-return-url.com/payments')
        ->amount(-5000);

})->throws(SatimUnexpectedValueException::class);
