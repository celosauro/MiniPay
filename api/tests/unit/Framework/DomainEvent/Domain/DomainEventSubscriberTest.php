<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\DomainEvent\Domain;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEventPublisher;
use MiniPay\Framework\DomainEvent\Domain\PersistDomainEventSubscriber;
use MiniPay\Framework\DomainEvent\Infrastructure\InMemoryEventStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DomainEventSubscriberTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function testPersistSubcriberHandle(): void
    {
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore($this->serializer);
        $event = new TestEvent(5, new DateTimeImmutable(), 1);
        $subscriber = new PersistDomainEventSubscriber($eventStore);

        $publisher = new DomainEventPublisher([], $eventBus);

        $publisher->subscribe($subscriber);
        $this->assertEmpty($eventStore->allStoredEvents());

        $publisher->publish($event);

        $this->assertCount(1, $eventStore->allStoredEvents());
    }
}
