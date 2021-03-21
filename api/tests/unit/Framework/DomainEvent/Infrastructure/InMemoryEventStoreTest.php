<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\DomainEvent\Infrastructure;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Infrastructure\InMemoryEventStore;
use MiniPay\Tests\Framework\DomainEvent\Domain\TestEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

use function get_class;

class InMemoryEventStoreTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    private function criarUmEvento(DateTimeImmutable $data, int $quantidade): DomainEvent
    {
        return new TestEvent(
            5,
            $data,
            $quantidade
        );
    }

    public function testAppend(): void
    {
        $eventStore = new InMemoryEventStore($this->serializer);

        $valor = 5;
        $quantidade = 6;
        $data = new DateTimeImmutable();
        $umEvento = new TestEvent(
            $valor,
            $data,
            $quantidade
        );

        $eventStore->append($umEvento);

        $eventos = $eventStore->allStoredEvents();

        $this->assertCount(1, $eventos);
        $this->assertEquals($umEvento->occurredOn(), $eventos[0]->occurredOn());
        $this->assertEquals(get_class($umEvento), $eventos[0]->typeName());
        $this->assertEquals($this->serializer->serialize($umEvento, 'json'), $eventos[0]->body());
    }

    public function testAllStoredEvents(): void
    {
        $eventStore = new InMemoryEventStore($this->serializer);

        $data = new DateTimeImmutable();
        $evento1 = $this->criarUmEvento($data, 1);
        $evento2 = $this->criarUmEvento($data, 2);
        $evento3 = $this->criarUmEvento($data, 3);

        $eventStore->append($evento1);
        $eventStore->append($evento2);
        $eventStore->append($evento3);

        $eventos = $eventStore->allStoredEvents();

        $this->assertCount(3, $eventos);

        $this->assertEquals($evento1->occurredOn(), $eventos[0]->occurredOn());
        $this->assertEquals(get_class($evento1), $eventos[0]->typeName());
        $this->assertEquals($this->serializer->serialize($evento1, 'json'), $eventos[0]->body());

        $this->assertEquals($evento2->occurredOn(), $eventos[1]->occurredOn());
        $this->assertEquals(get_class($evento2), $eventos[1]->typeName());
        $this->assertEquals($this->serializer->serialize($evento2, 'json'), $eventos[0]->body());

        $this->assertEquals($evento3->occurredOn(), $eventos[2]->occurredOn());
        $this->assertEquals(get_class($evento3), $eventos[2]->typeName());
        $this->assertEquals($this->serializer->serialize($evento3, 'json'), $eventos[0]->body());
    }
}
