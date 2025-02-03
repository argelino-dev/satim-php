<?php

declare(strict_types=1);

namespace PiteurStudio\Tests\Helpers;

use PiteurStudio\SatimPayHelper;

class SatimPayHelperTestClass
{
    use SatimPayHelper;

    public string $context = ''; // Initialize with empty string

    private ?array $registerOrderResponse = null;

    protected ?array $confirmOrderResponse = null;

    protected ?array $statusOrderResponse = null;

    protected ?array $refundOrderResponse = null;

    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    public function setResponse(string $context, array $response): void
    {
        $this->{$context.'OrderResponse'} = $response;
    }
}
