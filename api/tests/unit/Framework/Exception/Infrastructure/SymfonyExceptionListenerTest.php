<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Infrastructure;

use Exception;
use MiniPay\Framework\Exception\Infrastructure\SymfonyExceptionListener;
use MiniPay\Tests\Framework\Exception\Domain\UserNotFound;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

use function json_encode;

class SymfonyExceptionListenerTest extends TestCase
{
    /**
     * @param string[] $expectedResponse
     *
     * @dataProvider additionProvider
     */
    public function testOnKernelException(Throwable $exception, array $expectedResponse): void
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();

        $request = Request::create('/');

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);

        $listener = new SymfonyExceptionListener(false);
        $listener->onKernelException($event);

        $response = $event->getResponse() ?? null;
        $responseContent = $response ?
            (string) $response->getContent()
            : '';
        $responseCode = $response
            ? $response->getStatusCode()
            : 0;

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedResponse) ?: '',
            $responseContent
        );
        $this->assertEquals($exception->getCode(), $responseCode);
    }

    /** @return array<mixed> */
    public function additionProvider(): array
    {
        return [
            'when is a generic exception ' => [
                new Exception('Internal Server Error', 500),
                ['detail' => 'Internal Server Error'],
            ],
            'when is a lcobucci exception' => [
                new UserNotFound('User was not found', 404),
                ['detail' => 'User was not found', 'title' => null, 'type' => null],
            ],
        ];
    }
}
