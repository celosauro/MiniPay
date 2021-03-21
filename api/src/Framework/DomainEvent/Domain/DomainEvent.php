<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredOn(): DateTimeImmutable;
}
