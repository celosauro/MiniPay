<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Event;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;

final class TransactionWithdrew implements DomainEvent
{
    public string $userId;
    public float $amount;

    public DateTimeImmutable $occurredOn;

    private function __construct(
        string $userId,
        float $amount,
        DateTimeImmutable $occurredOn
    ) {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->occurredOn = $occurredOn;
    }

    public static function create(
        string $userId,
        float $amount,
        DateTimeImmutable $occurredOn
    ): self {
        return new self(
            $userId,
            $amount,
            $occurredOn
        );
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
