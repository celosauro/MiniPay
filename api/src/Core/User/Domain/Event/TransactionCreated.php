<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Event;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;

final class TransactionCreated implements DomainEvent
{
    public string $payerId;
    public string $payeeId;
    public float $value;

    public DateTimeImmutable $occurredOn;

    private function __construct(
        string $payerId,
        string $payeeId,
        float $value,
        DateTimeImmutable $occurredOn
    ) {
        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
        $this->value = $value;
        $this->occurredOn = $occurredOn;
    }

    public static function create(
        string $payerId,
        string $payeeId,
        float $value,
        DateTimeImmutable $occurredOn
    ): self {
        return new self(
            $payerId,
            $payeeId,
            $value,
            $occurredOn
        );
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
