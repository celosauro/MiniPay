<?php

declare(strict_types=1);

namespace MiniPay\Framework\Id\Domain;

use Ramsey\Uuid\Uuid;

use function is_string;

/**
 * @template T
 * @psalm-immutable
 */
final class Id
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @psalm-return Id<T>
     */
    public static function fromString(string $id): self
    {
        if (empty($id)) {
            throw InvalidId::fromEmptyString();
        }

        return new self($id);
    }

    /**
     * @psalm-return Id<T>
     */
    public static function generate(): self
    {
        $id = Uuid::uuid4();

        return new self($id->toString());
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @psalm-param Id<T>|string $id
     */
    public function isEqualTo($id): bool
    {
        if (is_string($id)) {
            return $this->toString() === $id;
        }

        return $this->toString() === $id->toString();
    }
}
