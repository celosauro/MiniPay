<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MiniPay\Core\User\Domain\Exception\TransactionReceivedNotificatorBadRequest;
use MiniPay\Core\User\Infrastructure\TransactionReceivedNotificatorclient;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class TransactionNotificatorclientTest extends TestCase
{
    /**
     * @test
     */
    public function shoudDoRequestToSendTransactionNotificationSucessfully(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                [],
                (string) json_encode(['message' => 'Enviado'])
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $clientHttp = new Client(['handler' => $handlerStack]);
        $transactionNotificatorClient = new TransactionReceivedNotificatorclient($clientHttp);

        $userId = Id::fromString('user-id')->toString();
        $amount = 100;

        $transactionNotificatorClient->send($userId, $amount);

        $this->assertEquals('{"message":"Enviado"}', $container[0]['response']->getBody()->getContents());
    }

    /**
     * @test
     */
    public function shoudDoRequestToSendTransactionNotificationFailed(): void
    {
        $this->expectException(TransactionReceivedNotificatorBadRequest::class);
        $this->expectExceptionMessage(
            'Fail to send transaction notification to userId user-id with amount 100.'
        );

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
        $transactionNotificatorClient = new TransactionReceivedNotificatorclient($clientHttp);

        $userId = Id::fromString('user-id')->toString();
        $amount = 100;

        $transactionNotificatorClient->send($userId, $amount);
    }
}
