<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Event;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;

final class UserCreated implements DomainEvent
{
    public string $userId;

    public DateTimeImmutable $occurredOn;

    private function __construct(
        string $userId,
        DateTimeImmutable $occurredOn
    ) {
        $this->userId = $userId;
        $this->occurredOn = $occurredOn;
    }

    public static function create(
        string $userId,
        DateTimeImmutable $occurredOn
    ): self {
        return new self(
            $userId,
            $occurredOn
        );
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
