<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\DomainEvent\Domain;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;

class TestEvent implements DomainEvent
{
    private DateTimeImmutable $occurredOn;
    private int $amount;
    private int $value;

    public function __construct(int $amount, DateTimeImmutable $occurredOn, int $value)
    {
        $this->amount = $amount;
        $this->value = $value;
        $this->occurredOn = $occurredOn;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
