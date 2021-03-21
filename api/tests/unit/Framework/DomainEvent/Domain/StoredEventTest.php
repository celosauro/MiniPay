<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\DomainEvent\Domain;

use DateTimeImmutable;
use MiniPay\Framework\DomainEvent\Domain\StoredEvent;
use PHPUnit\Framework\TestCase;

class StoredEventTest extends TestCase
{
    public function testCreateStoredEvent(): void
    {
        $ocurredOn = new DateTimeImmutable();
        $typeName = StoredEvent::class;
        $body = '{ requestBody }';

        $anEvent = new StoredEvent(
            $typeName,
            $ocurredOn,
            $body
        );

        $this->assertEquals($typeName, $anEvent->typeName());
        $this->assertEquals($ocurredOn, $anEvent->occurredOn());
        $this->assertEquals($body, $anEvent->body());
    }
}
