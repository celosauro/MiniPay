<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

class SymfonyMessengerErroUnpacker implements ErrorHandler
{
    private ErrorHandler $handler;

    public function __construct(ErrorHandler $handler)
    {
        $this->handler = $handler;
    }

    public function canHandleWith(Throwable $exception): bool
    {
        $canHandle = $this->handler->canHandleWith($exception);

        if ($canHandle) {
            return true;
        }

        if (! $exception instanceof HandlerFailedException) {
            return false;
        }

        $nestedException = $exception->getNestedExceptions()[0];

        return $this->handler->canHandleWith($nestedException);
    }

    public function handle(Throwable $exception): JsonResponse
    {
        if (! $this->canHandleWith($exception)) {
            throw new InvalidArgumentException('This error cannot be handled');
        }

        $canHandle = $this->handler->canHandleWith($exception);

        if ($canHandle) {
            return $this->handler->handle($exception);
        }

        $nestedException = $exception instanceof HandlerFailedException
            ? $exception->getNestedExceptions()[0]
            : $exception;

        return $this->handler->handle($nestedException);
    }
}
