<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

class PersistDomainEventSubscriber implements DomainEventSubscriber
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function handle(DomainEvent $event): void
    {
        $this->eventStore->append($event);
    }
}
