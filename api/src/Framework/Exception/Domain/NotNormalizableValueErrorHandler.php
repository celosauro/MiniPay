<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Throwable;

use function get_class;
use function sprintf;

class NotNormalizableValueErrorHandler implements ErrorHandler
{
    public function canHandleWith(Throwable $exception): bool
    {
        return $exception instanceof NotNormalizableValueException;
    }

    public function handle(Throwable $exception): JsonResponse
    {
        if (! $this->canHandleWith($exception)) {
            throw new InvalidArgumentException(sprintf('Error %s cannot be handled.', get_class($exception)));
        }

        return new JsonResponse(['detail' => 'Bad Request'], Response::HTTP_BAD_REQUEST);
    }
}
