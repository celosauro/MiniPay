<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Id\Domain;

use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Framework\Id\Domain\InvalidId;

class IdTest extends TestCase
{
    public function testGenerateId(): void
    {
        $id = Id::generate();

        $this->assertNotEmpty($id->toString());
    }

    public function testIdConstructor(): void
    {
        $anId = Id::fromString('an-id');

        $this->assertEquals($anId->toString(), 'an-id');
    }

    public function testIdNotEquals(): void
    {
        $anId = Id::fromString('an-id');
        $anotherId = Id::fromString('another-id');

        $this->assertNotEquals($anId->toString(), $anotherId->toString());
    }

    public function testInvalidId(): void
    {
        $this->expectException(InvalidId::class);

        Id::fromString('');
    }
}
