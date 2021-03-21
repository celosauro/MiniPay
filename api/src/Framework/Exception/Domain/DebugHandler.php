<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

use function get_class;
use function json_decode;

class DebugHandler implements ErrorHandler
{
    private ErrorHandler $handler;

    public function __construct(ErrorHandler $handler)
    {
        $this->handler = $handler;
    }

    public function canHandleWith(Throwable $exception): bool
    {
        return $this->handler->canHandleWith($exception);
    }

    public function handle(Throwable $exception): JsonResponse
    {
         return $this->handleWithInfo($exception);
    }

    /** @return array<string, string|int|array|null> */
    protected function generateExceptionInfo(Throwable $exception): array
    {
        $info = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'previous' => null,
        ];

        if (! $exception->getPrevious() instanceof Throwable) {
            return $info;
        }

        $info['previous'] = $this->generateExceptionInfo($exception->getPrevious());

        return $info;
    }

    protected function handleWithInfo(Throwable $exception): JsonResponse
    {
        $response = $this->handler->handle($exception);

        $data = json_decode((string) $response->getContent());
        $data->debug = $this->generateExceptionInfo($exception);
        $response->setData($data);

        return $response;
    }
}
