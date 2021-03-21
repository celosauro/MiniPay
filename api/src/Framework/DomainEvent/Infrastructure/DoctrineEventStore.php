<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Infrastructure;

use Doctrine\Persistence\ObjectManager;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\DomainEvent\Domain\StoredEvent;
use Symfony\Component\Serializer\SerializerInterface;

use function get_class;

class DoctrineEventStore implements EventStore
{
    private const ENTITY = StoredEvent::class;

    private ObjectManager $objectManager;

    private SerializerInterface $serializer;

    public function __construct(ObjectManager $objectManager, SerializerInterface $serializer)
    {
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
    }

    public function append(DomainEvent $aDomainEvent): void
    {
        $this->objectManager->persist(new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $this->serializer->serialize($aDomainEvent, 'json')
        ));
    }

    /**
     * @return StoredEvent[]
     */
    public function allStoredEvents(): array
    {
        return $this->objectManager->getRepository(self::ENTITY)->findAll();
    }
}
