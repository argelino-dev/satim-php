<?php


use PiteurStudio\Exception\SatimInvalidArgumentException;
use PiteurStudio\Satim;

it('cant not confirm without orderId' , function(){

    $satim = new Satim([
        'username' => '123456',
        'password' => '123456',
        'terminal_id' => '123456',
    ]);

    $satim->confirm('');


})->throws(SatimInvalidArgumentException::class);
