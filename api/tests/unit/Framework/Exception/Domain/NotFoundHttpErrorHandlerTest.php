<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Exception;
use InvalidArgumentException;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\NotFoundHttpErrorHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

use function json_encode;

class NotFoundHttpErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new NotFoundHttpErrorHandler();
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
        return [
            'when there is a detail' => [
                new NotFoundHttpException('No route found for \"GET /pagadores/810.131.190-45/ativardsds/'),
                ['detail' => 'No route found for \"GET /pagadores/810.131.190-45/ativardsds/'],
                404,
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
