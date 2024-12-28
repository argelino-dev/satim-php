<?php


use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Satim;

it('cant not refund without orderId', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->refund('' , 1000);


})->throws(SatimInvalidArgumentException::class);

it('cant not get refund with invalid positive amount', function () {

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->refund('123456' , -5);


})->throws(SatimInvalidArgumentException::class);
