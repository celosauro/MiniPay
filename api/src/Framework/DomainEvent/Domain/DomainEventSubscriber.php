<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

interface DomainEventSubscriber
{
    public function handle(DomainEvent $event): void;
}
