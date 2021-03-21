<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Exception;
use InvalidArgumentException;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\HandlerFailedErrorHandler;
use MiniPay\Framework\Exception\Domain\LcobucciErrorHandler;
use MiniPay\Framework\Exception\Domain\SymfonyMessengerErroUnpacker;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

use function json_encode;

class SymfonyMessengerErroUnpackerTest extends TestCase
{
    /** @dataProvider providerSupportedExceptions */
    public function testShouldIndicateCanHandleException(ErrorHandler $errorHandler, Throwable $exception): void
    {
        $handler = new SymfonyMessengerErroUnpacker($errorHandler);
        $canDeal = $handler->canHandleWith($exception);

        $this->assertEquals(true, $canDeal);
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldIndicateCannotHandleException(ErrorHandler $errorHandler, Throwable $exception): void
    {
        $handler = new SymfonyMessengerErroUnpacker($errorHandler);
        $canDeal = $handler->canHandleWith($exception);

        $this->assertEquals(false, $canDeal);
    }

    /**
     * @param string[] $expectedResponse
     *
     * @dataProvider providerSupportedExceptions
     */
    public function testShouldHandleException(
        ErrorHandler $errorHandler,
        Throwable $exception,
        array $expectedResponse,
        int $expectedStatusCode
    ): void {
        $handler = new SymfonyMessengerErroUnpacker($errorHandler);

        $response = $handler->handle($exception);

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedResponse) ?: '',
            $response->getContent() ?: ''
        );
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldNotHandleException(ErrorHandler $errorHandler, Throwable $error): void
    {
        $this->expectException(InvalidArgumentException::class);

        $handler = new LcobucciErrorHandler();
        $handler->handle($error);
    }

    /** @return array<mixed> */
    public function providerSupportedExceptions(): array
    {
        return [
            'when there is complete error information' => [
                new LcobucciErrorHandler(),
                InsufficientCredit::forPurchase(30, 50),
                [
                    'detail' => 'Your current balance is 30, but that costs 50.',
                    'type' => 'https://example.com/probs/out-of-credit',
                    'title' => 'You do not have enough credit.',
                    'balance' => 30,
                ],
                403,
            ],
            'when is custom exception into a HandlerFailedException' => [
                new LcobucciErrorHandler(),
                new HandlerFailedException(
                    new Envelope(new stdClass()),
                    [InsufficientCredit::forPurchase(30, 50)]
                ),
                [
                    'detail' => 'Your current balance is 30, but that costs 50.',
                    'type' => 'https://example.com/probs/out-of-credit',
                    'title' => 'You do not have enough credit.',
                    'balance' => 30,
                ],
                403,
            ],
        ];
    }

    /** @return array<mixed> */
    public function providerNotSupportedExceptions(): array
    {
        $errorHandler = new HandlerFailedErrorHandler();

        return [
            'when is an Exception' => [$errorHandler, new Exception()],
            'when is a RuntimeException' => [$errorHandler, new RuntimeException()],
            'when is a InvalidArgumentException' => [$errorHandler, new InvalidArgumentException()],
        ];
    }
}
