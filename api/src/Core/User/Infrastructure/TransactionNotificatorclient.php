<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MiniPay\Core\User\Domain\Exception\TransactionNotificatorBadRequest;
use MiniPay\Core\User\Domain\Notificator;
use Throwable;

class TransactionNotificatorclient implements Notificator
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
            'GET',
            self::CLIENT_URL
        );

        try {
            $this->httpClient->send($request);
        } catch (Throwable $t) {
            throw TransactionNotificatorBadRequest::forTransactionReceveid($userId, $amount);
        }
    }
}
