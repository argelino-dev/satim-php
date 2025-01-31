<?php

namespace PiteurStudio\Tests\Helpers;

use PiteurStudio\SatimConfig;

class SatimConfigTestClass extends SatimConfig
{
    public function __construct(array $config = [])
    {
        $this->initFromArray($config);
    }
}
