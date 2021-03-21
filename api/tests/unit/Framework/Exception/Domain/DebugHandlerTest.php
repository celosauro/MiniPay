<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Exception\Domain\DebugHandler;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\GenericErrorHandler;
use MiniPay\Framework\Exception\Domain\HandlerFailedErrorHandler;
use MiniPay\Framework\Exception\Domain\SymfonyMessengerErroUnpacker;
use RuntimeException;
use Throwable;

use function assert;
use function json_decode;

class DebugHandlerTest extends TestCase
{
    /** @dataProvider providerSupportedExceptions */
    public function testShouldIndicateCanHandleException(ErrorHandler $errorHandler, Throwable $exception): void
    {
        $handler = new DebugHandler(new SymfonyMessengerErroUnpacker($errorHandler));
        $canDeal = $handler->canHandleWith($exception);

        $this->assertEquals(true, $canDeal);
    }

    /**
     * @param array<mixed> $expectedResponse
     *
     * @dataProvider providerSupportedExceptions
     */
    public function testShouldHandleException(
        ErrorHandler $errorHandler,
        Throwable $exception,
        array $expectedResponse,
        int $expectedStatusCode
    ): void {
        $handler = new DebugHandler($errorHandler);

        $response = $handler->handle($exception);

        $this->assertEquals($expectedResponse, json_decode((string) $response->getContent(), true));

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    /** @return array<mixed> */
    public function providerSupportedExceptions(): array
    {
        $provider = [];

        $addPreviousException = static function (
            int $numberOfPreviousException,
            int &$lineOfPreviousException
        ) use (&$addPreviousException) {
            if ($numberOfPreviousException === 0) {
                return null;
            }

            $lineOfPreviousException = __LINE__ + 2;

            return new Exception(
                $numberOfPreviousException . ' Previous Internal Server Error',
                500,
                $addPreviousException($numberOfPreviousException - 1, $lineOfPreviousException)
            );
        };

        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        for ($i = 0; $i <= 4; $i++) {
            $lineOfPreviousException = 0;
            $lineOfException = __LINE__ + 3;
            $provider['when there is complete error information with' . $i . ' previous exception'] = [
                new GenericErrorHandler($newRelicLogger),
                new Exception('Internal Server Error', 500, $addPreviousException($i, $lineOfPreviousException)),
                [
                    'detail' => 'Internal Server Error',
                    'debug' => $this->addExpectedDebugInformation(
                        'Internal Server Error',
                        $lineOfException,
                        $i,
                        $lineOfPreviousException
                    ),
                ],
                500,
            ];
        }

        return $provider;
    }

    /** @return array<string, string|int|array|null> */
    private function addExpectedDebugInformation(
        string $message,
        int $lineOfException,
        int $numberOfPreviousException,
        int $lineOfPreviousException
    ): array {
        $previous = null;

        if ($numberOfPreviousException > 0) {
            $previous = $this->addExpectedDebugInformation(
                $numberOfPreviousException . ' Previous Internal Server Error',
                $lineOfPreviousException,
                $numberOfPreviousException - 1,
                $lineOfPreviousException
            );
        }

        return [
            'class' => 'Exception',
            'file' => __FILE__,
            'line' => $lineOfException,
            'message' => $message,
            'previous' => $previous,
        ];
    }

    /** @return array<mixed> */
    public function providerNotSupportedExceptions(): array
    {
        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        $errorHandler = new HandlerFailedErrorHandler($newRelicLogger);

        return [
            'when is an Exception' => [$errorHandler, new Exception()],
            'when is a RuntimeException' => [$errorHandler, new RuntimeException()],
            'when is a InvalidArgumentException' => [$errorHandler, new InvalidArgumentException()],
        ];
    }
}
