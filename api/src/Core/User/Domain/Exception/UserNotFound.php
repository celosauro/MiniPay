<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\ResourceNotFound;
use Lcobucci\ErrorHandling\Problem\Titled;
use MiniPay\Framework\Id\Domain\Id;
use RuntimeException;

final class UserNotFound extends RuntimeException implements ResourceNotFound, Titled, Detailed
{
    private string $id;

    public static function withId(string $id): self
    {
        $exception = new self(
            sprintf('User not found with given ID %s.', $id)
        );
        $exception->id = $id;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'User not found';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, float>
     */
    public function getExtraDetails(): array
    {
        return ['User ID' => $this->id];
    }
}
