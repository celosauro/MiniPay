<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

use function get_class;
use function sprintf;

class HandlerFailedErrorHandler implements ErrorHandler
{
    public function canHandleWith(Throwable $exception): bool
    {
        return $exception instanceof HandlerFailedException;
    }

    public function handle(Throwable $exception): JsonResponse
    {
        if (! $this->canHandleWith($exception)) {
            throw new InvalidArgumentException(sprintf('Error %s cannot be handled.', get_class($exception)));
        }

        $firstException = $exception instanceof HandlerFailedException
            ? $exception->getNestedExceptions()[0]
            : $exception;

        return new JsonResponse(['detail' => $firstException->getMessage()], Response::HTTP_BAD_REQUEST);
    }
}
