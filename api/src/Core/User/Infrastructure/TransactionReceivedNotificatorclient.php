<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MiniPay\Core\User\Domain\Exception\TransactionReceivedNotificatorBadRequest;
use MiniPay\Core\User\Domain\TransactionReceivedNotificator;
use Throwable;

use function json_encode;

class TransactionReceivedNotificatorclient implements TransactionReceivedNotificator
{
    private const CLIENT_URL = 'https://run.mocky.io/v3/b19f7b9f-9cbf-4fc6-ad22-dc30601aec04';

    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function send(string $userId, float $amount): void
    {
        $request = new Request(
            'POST',
            self::CLIENT_URL,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'userId' => $userId,
                'amount' => $amount,
            ])
        );

        try {
            $this->httpClient->send($request);
        } catch (Throwable $t) {
            throw TransactionReceivedNotificatorBadRequest::forTransactionReceveid($userId, $amount);
        }
    }
}
