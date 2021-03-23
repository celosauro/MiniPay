<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\Transaction\Infrastructure\Controller;

use MiniPay\Tests\Framework\DoctrineTestCase;

use function json_encode;

class TransactionControllerTest extends DoctrineTestCase
{
    /**
     * @test
     */
    public function shoudSendMoney(): void
    {
        $this->createAnDefaultUser('55094850008', 'foo@bar.com');
        $this->createAnDefaultUser('18212285022', 'foobar@bar.com');

        $content = (string) json_encode([
            'cpf_cnpj' => '55094850008',
            'value' => 99,
        ]);

        $client = self::$client;
        $client->request(
            'POST',
            '/transaction',
            [],
            [],
            [],
            $content
        );

        dd($client->getResponse()->getContent());

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    private function createAnDefaultUser(string $cpfOrCnpf, string $email): void
    {
        $content = (string) json_encode([
            'cpf_cnpj' => $cpfOrCnpf,
            'full_name' => 'Foo Bar',
            'email' => $email,
            'wallet_amount' => 99,
            'type' => 'default',
        ]);

        $client = self::$client;
        $client->request(
            'POST',
            '/users',
            [],
            [],
            [],
            $content
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }
}
