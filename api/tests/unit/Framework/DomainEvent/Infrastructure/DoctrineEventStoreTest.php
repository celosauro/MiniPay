<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\DomainEvent\Infrastructure;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\DomainEvent\Infrastructure\DoctrineEventStore;
use MiniPay\Tests\Framework\DoctrineTestCase;
use MiniPay\Tests\Framework\DomainEvent\Domain\TestEvent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

use function get_class;

class DoctrineEventStoreTest extends DoctrineTestCase
{
    private EventStore $eventStore;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $this->eventStore = new DoctrineEventStore($this->entityManager, $serializer);
        $this->serializer = $serializer;
    }

    private function createEvent(int $amount, DateTimeImmutable $ocurredOn, int $value): DomainEvent
    {
        return new TestEvent(
            $amount,
            $ocurredOn,
            $value
        );
    }

    public function testAppend(): void
    {
        $amount = 6;
        $ocurredOn = new DateTimeImmutable();
        $value = 5;
        $anEvent = new TestEvent(
            $amount,
            $ocurredOn,
            $value,
        );

        $this->eventStore->append($anEvent);
        $this->entityManager->flush();

        $events = $this->eventStore->allStoredEvents();

        $this->assertCount(1, $events);
        $this->assertEquals($anEvent->occurredOn(), $events[0]->occurredOn());
        $this->assertEquals(get_class($anEvent), $events[0]->typeName());
        $this->assertEquals($this->serializer->serialize($anEvent, 'json'), $events[0]->body());
    }

    public function testAllStoredEvents(): void
    {
        $ocurredOn = new DateTimeImmutable();
        $event1 = $this->createEvent(1, $ocurredOn, 1);
        $event2 = $this->createEvent(2, $ocurredOn, 2);
        $event3 = $this->createEvent(3, $ocurredOn, 3);

        $this->eventStore->append($event1);
        $this->eventStore->append($event2);
        $this->eventStore->append($event3);

        $this->entityManager->flush();

        $events = $this->eventStore->allStoredEvents();

        $this->assertCount(3, $events);

        $this->assertEquals($event1->occurredOn(), $events[0]->occurredOn());
        $this->assertEquals(get_class($event1), $events[0]->typeName());
        $this->assertEquals($this->serializer->serialize($event1, 'json'), $events[0]->body());

        $this->assertEquals($event2->occurredOn(), $events[1]->occurredOn());
        $this->assertEquals(get_class($event2), $events[1]->typeName());
        $this->assertEquals($this->serializer->serialize($event2, 'json'), $events[1]->body());

        $this->assertEquals($event3->occurredOn(), $events[2]->occurredOn());
        $this->assertEquals(get_class($event3), $events[2]->typeName());
        $this->assertEquals($this->serializer->serialize($event3, 'json'), $events[2]->body());
    }
}
