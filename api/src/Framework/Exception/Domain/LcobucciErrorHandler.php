<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use InvalidArgumentException;
use Lcobucci\ErrorHandling\Problem\AuthorizationRequired;
use Lcobucci\ErrorHandling\Problem\Conflict;
use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Forbidden;
use Lcobucci\ErrorHandling\Problem\InvalidRequest;
use Lcobucci\ErrorHandling\Problem\ResourceNoLongerAvailable;
use Lcobucci\ErrorHandling\Problem\ResourceNotFound;
use Lcobucci\ErrorHandling\Problem\ServiceUnavailable;
use Lcobucci\ErrorHandling\Problem\Titled;
use Lcobucci\ErrorHandling\Problem\Typed;
use Lcobucci\ErrorHandling\Problem\UnprocessableRequest;
use Lcobucci\ErrorHandling\StatusCodeExtractionStrategy\ClassMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

use function get_class;
use function sprintf;

class LcobucciErrorHandler implements ErrorHandler
{
    public function canHandleWith(Throwable $exception): bool
    {
        switch (true) {
            case $exception instanceof InvalidRequest:
            case $exception instanceof AuthorizationRequired:
            case $exception instanceof Forbidden:
            case $exception instanceof ResourceNotFound:
            case $exception instanceof Conflict:
            case $exception instanceof ResourceNoLongerAvailable:
            case $exception instanceof UnprocessableRequest:
            case $exception instanceof ServiceUnavailable:
                return true;

            default:
                return false;
        }
    }

    public function handle(Throwable $error): JsonResponse
    {
        if (! $this->canHandleWith($error)) {
            throw new InvalidArgumentException(sprintf('Error %s cannot be handled.', get_class($error)));
        }

        $data = [
            'type' => $error instanceof Typed ? $error->getTypeUri() : null,
            'title' => $error instanceof Titled ? $error->getTitle() : null,
            'detail' => $error->getMessage(),
        ];

        if ($error instanceof Detailed) {
            $data += $error->getExtraDetails();
        }

        return new JsonResponse(
            $data,
            (new ClassMap())->extractStatusCode($error),
        );
    }
}
