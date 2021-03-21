<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Lcobucci\ErrorHandling\Problem\ResourceNotFound;
use RuntimeException;

final class UserNotFound extends RuntimeException implements ResourceNotFound
{
}
