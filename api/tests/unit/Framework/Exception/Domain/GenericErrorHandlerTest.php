<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\GenericErrorHandler;

use function assert;
use function json_encode;

class GenericErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        $this->handler = new GenericErrorHandler($newRelicLogger);
    }

    public function testCanHandleWithShouldReturnTrueForBaseExceptionClass(): void
    {
        $canDeal = $this->handler->canHandleWith(new Exception());

        $this->assertEquals(true, $canDeal);
    }

    public function testCanHandleWithShouldReturnTrueForBaseErrorClass(): void
    {
        $canDeal = $this->handler->canHandleWith(new Error());
        $this->assertEquals(true, $canDeal);
    }

    public function testShouldHandleTheBaseException(): void
    {
        $response = $this->handler->handle(new Exception());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['detail' => 'Internal Server Error']) ?: '',
            $response->getContent() ?: ''
        );
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testShouldHandleTheBaseError(): void
    {
        $response = $this->handler->handle(new Error());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['detail' => 'Internal Server Error']) ?: '',
            $response->getContent() ?: ''
        );
        $this->assertEquals(500, $response->getStatusCode());
    }
}
