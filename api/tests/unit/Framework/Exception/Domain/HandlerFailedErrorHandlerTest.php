<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Exception;
use InvalidArgumentException;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\HandlerFailedErrorHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

use function json_encode;

class HandlerFailedErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new HandlerFailedErrorHandler();
    }

    /** @dataProvider providerSupportedExceptions */
    public function testShouldIndicateCanHandleException(Throwable $exception): void
    {
        $canDeal = $this->handler->canHandleWith($exception);

        $this->assertEquals(true, $canDeal);
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldIndicateCannotHandleException(Throwable $exception): void
    {
        $canDeal = $this->handler->canHandleWith($exception);

        $this->assertEquals(false, $canDeal);
    }

    /**
     * @param string[] $expectedResponse
     *
     * @dataProvider providerSupportedExceptions
     */
    public function testShouldHandleException(
        Throwable $exception,
        array $expectedResponse,
        int $expectedStatusCode
    ): void {
        $response = $this->handler->handle($exception);

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedResponse) ?: '',
            $response->getContent() ?: ''
        );
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldNotHandleException(Throwable $error): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->handler->handle($error);
    }

    /** @return array<mixed> */
    public function providerSupportedExceptions(): array
    {
        $envelope = new Envelope(new stdClass());

        return [
            'when there is just a detail' => [
                new HandlerFailedException(
                    $envelope,
                    [new RuntimeException('Error to access invalid handle')]
                ),
                ['detail' => 'Error to access invalid handle'],
                400,
            ],
        ];
    }

    /** @return array<mixed> */
    public function providerNotSupportedExceptions(): array
    {
        return [
            'when is an Exception' => [new Exception()],
            'when is a RuntimeException' => [new RuntimeException()],
            'when is a InvalidArgumentException' => [new InvalidArgumentException()],
        ];
    }
}
