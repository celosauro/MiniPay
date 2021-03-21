<?php

declare(strict_types=1);

namespace MiniPay\Framework\Id\Domain;

use Lcobucci\ErrorHandling\Problem\InvalidRequest;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

class InvalidId extends RuntimeException implements InvalidRequest, Titled
{
    public static function fromEmptyString(string $idDescription = 'id'): self
    {
        return new self(sprintf('The given %s is invalid because it is empty', $idDescription));
    }

    public function getTitle(): string
    {
        return 'Invalid id';
    }
}
