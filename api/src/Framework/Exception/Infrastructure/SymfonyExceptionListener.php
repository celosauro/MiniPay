<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Infrastructure;

use MiniPay\Framework\Exception\Domain\DebugHandler;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\GenericErrorHandler;
use MiniPay\Framework\Exception\Domain\LcobucciErrorHandler;
use MiniPay\Framework\Exception\Domain\NotEncodableValueErrorHandler;
use MiniPay\Framework\Exception\Domain\NotFoundHttpErrorHandler;
use MiniPay\Framework\Exception\Domain\NotNormalizableValueErrorHandler;
use MiniPay\Framework\Exception\Domain\SymfonyMessengerErroUnpacker;
use MiniPay\Framework\Exception\Domain\ValidationFailedErrorHandler;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class SymfonyExceptionListener
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $handlers = [
            new SymfonyMessengerErroUnpacker(new LcobucciErrorHandler()),
            new SymfonyMessengerErroUnpacker(new ValidationFailedErrorHandler()),
            new SymfonyMessengerErroUnpacker(new NotEncodableValueErrorHandler()),
            new SymfonyMessengerErroUnpacker(new NotNormalizableValueErrorHandler()),
            new SymfonyMessengerErroUnpacker(new NotFoundHttpErrorHandler()),
        ];

        foreach ($handlers as $handler) {
            if (! $handler->canHandleWith($exception)) {
                continue;
            }

            $this->attachDebugHandlerIfDebugIsEnabled($handler);

            $event->setResponse($handler->handle($exception));

            return;
        }

        $genericException = new GenericErrorHandler();
        $response = $genericException->handle($exception);

        $event->setResponse($response);
    }

    private function attachDebugHandlerIfDebugIsEnabled(ErrorHandler &$errorHandler): void
    {
        if (! $this->debug) {
            return;
        }

        $errorHandler = new DebugHandler($errorHandler);
    }
}
