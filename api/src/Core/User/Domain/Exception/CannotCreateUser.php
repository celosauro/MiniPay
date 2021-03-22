<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\InvalidRequest;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class CannotCreateUser extends RuntimeException implements InvalidRequest, Titled, Detailed
{
    private string $type;

    public static function withType(string $type): self
    {
        $exception = new self(
            sprintf('Cannot create user with given type %s.', $type)
        );
        $exception->type = $type;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'Cannot create user';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    public function getExtraDetails(): array
    {
        return [
            'Type' => $this->type,
        ];
    }
}
