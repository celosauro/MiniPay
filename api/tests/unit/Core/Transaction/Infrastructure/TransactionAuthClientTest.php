<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\Transaction\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MiniPay\Core\Transaction\Infrastructure\TransactionAuthClient;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class TransactionAuthClientTest extends TestCase
{
    /**
     * @test
     */
    public function shoudDoRequestToTransactionAuthSucessfully(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                [],
                (string) json_encode(['message' => 'Autorizado'])
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $clientHttp = new Client(['handler' => $handlerStack]);
        $transactionAuthClient = new TransactionAuthClient($clientHttp);

        $authorized = $transactionAuthClient->auth();

        $this->assertTrue($authorized);
    }

    /**
     * @test
     */
    public function shoudDoRequestToTransactionAuthFailed(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                401,
                [],
                (string) json_encode(['message' => 'Não Autorizado'])
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $clientHttp = new Client(['handler' => $handlerStack]);
        $transactionAuthClient = new TransactionAuthClient($clientHttp);

        $authorized = $transactionAuthClient->auth();

        $this->assertFalse($authorized);
    }
}
