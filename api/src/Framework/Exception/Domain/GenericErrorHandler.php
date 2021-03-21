<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Fig\Http\Message\StatusCodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class GenericErrorHandler implements ErrorHandler
{
    private NewRelicInteractorInterface $newRelicLogger;

    public function __construct(NewRelicInteractorInterface $newRelicLogger)
    {
        $this->newRelicLogger = $newRelicLogger;
    }

    public function canHandleWith(Throwable $exception): bool
    {
        return $exception instanceof Throwable;
    }

    public function handle(Throwable $error): JsonResponse
    {
        $this->newRelicLogger->noticeThrowable($error);

        return new JsonResponse(
            ['detail' => 'Internal Server Error'],
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
        );
    }
}
