<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Infrastructure;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use InvalidArgumentException;
use MiniPay\Framework\Exception\Domain\DebugHandler;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\GenericErrorHandler;
use MiniPay\Framework\Exception\Domain\LcobucciErrorHandler;
use MiniPay\Framework\Exception\Domain\NotEncodableValueErrorHandler;
use MiniPay\Framework\Exception\Domain\NotFoundHttpErrorHandler;
use MiniPay\Framework\Exception\Domain\SymfonyMessengerErroUnpacker;
use MiniPay\Framework\Exception\Domain\ValidationFailedErrorHandler;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

use function implode;
use function in_array;
use function sprintf;

class SymfonyExceptionListener
{
    /**
     * In DEVELOPMENT mode:
     *  - handle exceptions: NOT ALL - only exceptions supported by configured handlers will be handled. Others
     *  exceptions will be ignored by the listener in order to allow Symfony to display its nice default Error Page.
     *  - attach debug information into HTTP Responses: YES
     */
    public const MODE_DEVELOPMENT = 'development';

    /**
     * In DEBUG mode:
     *  - handle exceptions: ALL
     *  - attach debug information into HTTP Responses: YES
     */
    public const MODE_DEBUG = 'debug';

    /**
     * In PRODUCTION mode:
     *  - handle exceptions: all
     *  - attach debug information into HTTP Responses: YES
     */
    public const MODE_PRODUCTION = 'production';

    private const MODES = [
        self::MODE_DEBUG,
        self::MODE_DEVELOPMENT,
        self::MODE_PRODUCTION,
    ];

    private NewRelicInteractorInterface $newRelicLogger;

    private string $mode;

    public function __construct(string $mode, NewRelicInteractorInterface $newRelicLogger)
    {
        if (in_array($mode, self::MODES, true) === false) {
            throw new InvalidArgumentException(sprintf(
                'The given mode (%s) is unknown. Supported modes: %s.',
                $mode,
                implode(', ', self::MODES)
            ));
        }

        $this->mode = $mode;
        $this->newRelicLogger = $newRelicLogger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $handlers = $this->configuredHandlers();

        foreach ($handlers as $handler) {
            if ($handler->canHandleWith($exception) === false) {
                continue;
            }

            $event->setResponse($handler->handle($exception));

            return;
        }
    }

    /**
     * @return array<ErrorHandler>
     */
    private function configuredHandlers(): array
    {
        $handlers = [
            new SymfonyMessengerErroUnpacker(new LcobucciErrorHandler($this->newRelicLogger)),
            new SymfonyMessengerErroUnpacker(new ValidationFailedErrorHandler($this->newRelicLogger)),
            new SymfonyMessengerErroUnpacker(new NotEncodableValueErrorHandler($this->newRelicLogger)),
            new SymfonyMessengerErroUnpacker(new NotFoundHttpErrorHandler($this->newRelicLogger)),
        ];

        if ($this->shouldHandleExceptionsNotSupportedByConfiguredHandlers()) {
            $handlers[] = new GenericErrorHandler($this->newRelicLogger);
        }

        if ($this->shouldAttachDebugInformationToResponse() === false) {
            return $handlers;
        }

        foreach ($handlers as $index => $handler) {
            $handlers[$index] = new DebugHandler($handler);
        }

        return $handlers;
    }

    private function shouldHandleExceptionsNotSupportedByConfiguredHandlers(): bool
    {
        $modesThatHandleUnsupportedExceptions = [
            self::MODE_DEBUG,
            self::MODE_PRODUCTION,
        ];

        return in_array($this->mode, $modesThatHandleUnsupportedExceptions, true);
    }

    private function shouldAttachDebugInformationToResponse(): bool
    {
        $modesThatAttachesDebugInformationIntoResponse = [
            self::MODE_DEVELOPMENT,
            self::MODE_DEBUG,
        ];

        return in_array($this->mode, $modesThatAttachesDebugInformationIntoResponse, true);
    }
}
