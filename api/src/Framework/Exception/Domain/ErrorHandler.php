<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

interface ErrorHandler
{
    public function canHandleWith(Throwable $exception): bool;

    public function handle(Throwable $exception): JsonResponse;
}
