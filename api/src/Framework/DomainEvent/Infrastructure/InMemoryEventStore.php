<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Infrastructure;

use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\DomainEvent\Domain\StoredEvent;
use Symfony\Component\Serializer\SerializerInterface;

use function get_class;

class InMemoryEventStore implements EventStore
{
    /** @var StoredEvent[] */
    private array $items;

    private SerializerInterface $serializer;

    /** @param StoredEvent[] $items */
    public function __construct(SerializerInterface $serializer, array $items = [])
    {
        $this->items = $items;
        $this->serializer = $serializer;
    }

    /**
     * @return StoredEvent[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function append(DomainEvent $aDomainEvent): void
    {
        $this->items[] = new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $this->serializer->serialize($aDomainEvent, 'json')
        );
    }

   /**
    * @return StoredEvent[]
    */
    public function allStoredEvents(): array
    {
        return $this->items;
    }
}
