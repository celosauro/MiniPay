<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

interface EventStore
{
    public function append(DomainEvent $aDomainEvent): void;

    /**
     * @return StoredEvent[]
     */
    public function allStoredEvents(): array;
}
