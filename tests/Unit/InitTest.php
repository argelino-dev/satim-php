<?php

use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Satim;

it('can not init without username', function () {

    new Satim([
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('can not init without password', function () {

    new Satim([
        'username' => '123456',
        'terminal_id' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('can not init without terminal_id', function () {

    new Satim([
        'username' => '123456',
        'password' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('cant init without valid data ', function () {

    new Satim([
        'username' => 123,
        'password' => [],
        'terminal_id' => new \Exception,
    ]);

})->throws(SatimInvalidArgumentException::class);

it('can init', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    expect($satim)->toBeInstanceOf(Satim::class);

});
