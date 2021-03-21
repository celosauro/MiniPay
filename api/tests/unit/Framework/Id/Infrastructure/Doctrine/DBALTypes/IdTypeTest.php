<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Id\Infrastructure\Doctrine\DBALTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Id\Domain\Id as ValueObject;
use MiniPay\Framework\Id\Infrastructure\Doctrine\DBALTypes\IdType;

use function assert;

class IdTypeTest extends TestCase
{
    /** @var AbstractPlatform&MockObject */
    private $platform;

    private Type $type;

    public static function setUpBeforeClass(): void
    {
        Type::addType('id', IdType::class);
    }

    protected function setUp(): void
    {
        /**
         * @var AbstractPlatform&MockObject
         */
        $this->platform = $this->getMockBuilder(AbstractPlatform::class)
            ->addMethods([])
            ->getMockForAbstractClass();

        $this->type = Type::getType('id');
    }

    public function testConvertAnInstanceOfValueObjectToDatabaseMustReturnANonemptyString(): void
    {
        $id = ValueObject::fromString('um-id');

        $actual = $this->type->convertToDatabaseValue($id, $this->platform);

        $this->assertEquals('um-id', $actual);
    }

    public function testConvertANullValueToDatabaseMustReturnNull(): void
    {
        $value = $this->type->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    public function testConvertANonemptyStringFromTheDatabaseMustReturnAValueObject(): void
    {
        $id = $this->type->convertToPHPValue('um-id', $this->platform);
        assert($id instanceof ValueObject);
        $this->assertInstanceOf(ValueObject::class, $id);
        $this->assertTrue($id->isEqualTo('um-id'));
    }

    public function testConvertANullValueFromTheDatabaseMustReturnNull(): void
    {
        $value = $this->type->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }
}
