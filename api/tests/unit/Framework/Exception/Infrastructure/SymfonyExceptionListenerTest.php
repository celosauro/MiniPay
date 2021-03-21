<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Infrastructure;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Exception\Infrastructure\SymfonyExceptionListener;
use MiniPay\Tests\Framework\Exception\Domain\UserNotFound;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

use function assert;
use function json_decode;

class SymfonyExceptionListenerTest extends TestCase
{
    /**
     * @dataProvider allSupportedModes
     */
    public function testGivenModeIsASupportedMode(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );

        $this->assertInstanceOf(SymfonyExceptionListener::class, $listener);
    }

    public function testShouldThrowExceptionWhenGivenModeDoesntExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $listener = new SymfonyExceptionListener(
            'nonexistent mode',
            $this->aNewRelicLogger()
        );
    }

    /**
     * @dataProvider modesThatHandleSupportedExceptions
     */
    public function testGivenModeShouldCreateResponseForSupportedException(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );
        $event = $this->aExceptionEventFor(new UserNotFound('User was not found'));

        $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }

    /**
     * @dataProvider modesThatHandleUnsupportedExceptions
     */
    public function testGivenModeShouldCreateResponseForUnsupportedException(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );
        $event = $this->aExceptionEventFor(new Exception('There is no handler for this exception class'));

        $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }

    /**
     * @dataProvider modesThatDoesntHandleUnsupportedExceptions
     */
    public function testGivenModeShouldNotCreateResponseForUnsupportedException(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );
        $event = $this->aExceptionEventFor(new Exception('There is no handler for this exception class'));

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * @dataProvider modesThatAppendDebugInformation
     */
    public function testGivenModeShouldAppendDebugInformationInRespose(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );
        $event = $this->aExceptionEventFor(new UserNotFound('User was not found'));

        $listener->onKernelException($event);

        $response = $event->getResponse();
        assert($response instanceof Response);
        $responseAsArray = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('debug', $responseAsArray);
        $this->assertNotEmpty($responseAsArray['debug']);
    }

    /**
     * @dataProvider modesThatDoesntAppendDebugInformation
     */
    public function testGivenModeShouldNotAppendDebugInformationInRespose(string $mode): void
    {
        $listener = new SymfonyExceptionListener(
            $mode,
            $this->aNewRelicLogger()
        );
        $event = $this->aExceptionEventFor(new UserNotFound('User was not found'));

        $listener->onKernelException($event);

        $response = $event->getResponse();
        assert($response instanceof Response);
        $responseAsArray = json_decode((string) $response->getContent(), true);
        $this->assertArrayNotHasKey('debug', $responseAsArray);
    }

    /**
     * @return array<mixed>
     */
    public function allSupportedModes(): array
    {
        return [
            'MODE_DEVELOPMENT' => [
                SymfonyExceptionListener::MODE_DEVELOPMENT,
            ],
            'MODE_DEBUG' => [
                SymfonyExceptionListener::MODE_DEBUG,
            ],
            'MODE_PRODUCTION' => [
                SymfonyExceptionListener::MODE_PRODUCTION,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function modesThatHandleSupportedExceptions(): array
    {
        return [
            'MODE_DEVELOPMENT' => [
                SymfonyExceptionListener::MODE_DEVELOPMENT,
            ],
            'MODE_DEBUG' => [
                SymfonyExceptionListener::MODE_DEBUG,
            ],
            'MODE_PRODUCTION' => [
                SymfonyExceptionListener::MODE_PRODUCTION,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function modesThatHandleUnsupportedExceptions(): array
    {
        return [
            'MODE_DEBUG' => [
                SymfonyExceptionListener::MODE_DEBUG,
            ],
            'MODE_PRODUCTION' => [
                SymfonyExceptionListener::MODE_PRODUCTION,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function modesThatDoesntHandleUnsupportedExceptions(): array
    {
        return [
            'MODE_DEVELOPMENT' => [
                SymfonyExceptionListener::MODE_DEVELOPMENT,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function modesThatAppendDebugInformation(): array
    {
        return [
            'MODE_DEVELOPMENT' => [
                SymfonyExceptionListener::MODE_DEVELOPMENT,
            ],
            'MODE_DEBUG' => [
                SymfonyExceptionListener::MODE_DEBUG,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function modesThatDoesntAppendDebugInformation(): array
    {
        return [
            'MODE_PRODUCTION' => [
                SymfonyExceptionListener::MODE_PRODUCTION,
            ],
        ];
    }

    private function aNewRelicLogger(): NewRelicInteractor
    {
        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        return $newRelicLogger;
    }

    private function aExceptionEventFor(Throwable $throwable): ExceptionEvent
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $request = Request::create('/');

        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $throwable);
    }
}
