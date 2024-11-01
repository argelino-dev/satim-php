<?php

use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\Satim;

it('can not init without username', function () {

    $satim = new Satim([
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('can not init without password', function () {

    $satim = new Satim([
        'username' => '123456',
        'terminal_id' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('can not init without terminal_id', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
    ]);

})->throws(SatimMissingDataException::class);

it('can init', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    expect($satim)->toBeInstanceOf(Satim::class);

});
