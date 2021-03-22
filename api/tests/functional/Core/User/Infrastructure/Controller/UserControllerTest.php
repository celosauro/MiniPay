<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Infrastructure\Controller;

use MiniPay\Tests\Framework\DoctrineTestCase;

use function json_encode;

class UserControllerTest extends DoctrineTestCase
{
    /**
     * @test
     */
    public function shoudCreateAnDefaultUser(): void
    {
        $content = (string) json_encode([
            'cpf_cnpj' => '55094850008',
            'full_name' => 'Foo Bar',
            'email' => 'email@email.com',
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

    /**
     * @test
     */
    public function shoudCreateAnStoreKeeperUser(): void
    {
        $content = (string) json_encode([
            'cpf_cnpj' => '55094850008',
            'full_name' => 'Foo Bar',
            'email' => 'email@email.com',
            'wallet_amount' => 99,
            'type' => 'storekeeper',
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
