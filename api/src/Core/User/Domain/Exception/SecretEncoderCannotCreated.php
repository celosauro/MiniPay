<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

final class SecretEncoderCannotCreated extends RuntimeException implements Titled
{
    public static function create(): self
    {
        return new self('Cannot create secret.');
    }

    public function getTitle(): string
    {
        return 'Cannot create secret.';
    }
}
