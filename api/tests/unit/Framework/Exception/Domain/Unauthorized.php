<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Lcobucci\ErrorHandling\Problem\AuthorizationRequired;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

class Unauthorized extends RuntimeException implements AuthorizationRequired, Titled
{
    public function getTitle(): string
    {
        return 'The credencial information is not valid.';
    }
}
