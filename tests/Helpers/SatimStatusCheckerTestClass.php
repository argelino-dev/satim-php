<?php

declare(strict_types=1);

namespace PiteurStudio\Tests\Helpers;

use PiteurStudio\Exception\SatimMissingDataException;
use PiteurStudio\SatimStatusChecker;

class SatimStatusCheckerTestClass
{
    use SatimStatusChecker;

    private ?array $response_data = null;

    public function setResponse(array $response): void
    {
        $this->response_data = $response;
    }

    public function getResponse(): array
    {
        if ($this->response_data === null) {
            throw new SatimMissingDataException('No response data found.');
        }

        return $this->response_data;
    }
}
