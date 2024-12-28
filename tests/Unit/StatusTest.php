<?php


use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Satim;

it('cant not get status without orderId', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->status('');


})->throws(SatimInvalidArgumentException::class);
