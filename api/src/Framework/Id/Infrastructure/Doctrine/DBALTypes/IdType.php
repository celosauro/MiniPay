<?php

declare(strict_types=1);

namespace MiniPay\Framework\Id\Infrastructure\Doctrine\DBALTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use MiniPay\Framework\Id\Domain\Id;

/**
 * @template T
 */
class IdType extends GuidType
{
    /**
     * @param mixed $value
     *
     * @psalm-return Id<T>|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        if (empty($value)) {
            return null;
        }

        return Id::fromString($value);
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (! $value instanceof Id) {
            return null;
        }

        return $value->toString();
    }
}
