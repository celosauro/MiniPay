<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MiniPay\Core\User\Domain\TransactionAuth;
use Psr\Http\Message\ResponseInterface;

use function json_decode;

class TransactionAuthClient implements TransactionAuth
{
    private const CLIENT_URL = 'https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6';

    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function auth(): bool
    {
        $request = new Request(
            'GET',
            self::CLIENT_URL
        );

        $response = $this->doRequest($request);
        $body = (string) $response->getBody();

        return $this->formatAuthResponse($body);
    }

    private function formatAuthResponse(string $body): bool
    {
        $body = json_decode($body, true);

        return $body['message'] === 'Autorizado';
    }

    private function doRequest(Request $request): ResponseInterface
    {
        return $this->httpClient->send($request, ['http_errors' => false]);
    }
}
