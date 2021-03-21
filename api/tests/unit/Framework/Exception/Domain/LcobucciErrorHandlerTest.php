<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\LcobucciErrorHandler;
use RuntimeException;
use Throwable;

use function assert;
use function json_encode;

class LcobucciErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        $this->handler = new LcobucciErrorHandler($newRelicLogger);
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
            'when there is just a detail' => [
                new UserNotFound('User not found'),
                ['detail' => 'User not found', 'type' => null, 'title' => null],
                404,
            ],
            'when there is just a detail and title' => [
                new Unauthorized('Invalid credencial'),
                [
                    'detail' => 'Invalid credencial',
                    'title' => 'The credencial information is not valid.',
                    'type' => null,
                ],
                401,
            ],
            'when there is complete error information' => [
                InsufficientCredit::forPurchase(30, 50),
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
        return [
            'when is an Exception' => [new Exception()],
            'when is a RuntimeException' => [new RuntimeException()],
            'when is a InvalidArgumentException' => [new InvalidArgumentException()],
        ];
    }
}
