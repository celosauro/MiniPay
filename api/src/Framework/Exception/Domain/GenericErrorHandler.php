<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use Fig\Http\Message\StatusCodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class GenericErrorHandler implements ErrorHandler
{
    public function canHandleWith(Throwable $exception): bool
    {
        return $exception instanceof Throwable;
    }

    public function handle(Throwable $error): JsonResponse
    {
        return new JsonResponse(
            ['detail' => 'Internal Server Error'],
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
        );
    }
}
