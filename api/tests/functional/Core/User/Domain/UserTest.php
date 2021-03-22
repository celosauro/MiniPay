<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Domain;

use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnUser(): void
    {
        $id = Id::fromString('user-id');
        $fullName = 'Foo Bar';
        $cpfOrCnpj = '88498957044';
        $email = 'foobar@test.com';
        $balance = 99.99;
        $account = new Wallet($balance);

        $user = User::create(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $account
        );

        $this->assertTrue($id->isEqualTo($user->id()));
        $this->assertEquals($fullName, $user->fullName());
        $this->assertEquals($cpfOrCnpj, $user->cpfOrCnpj());
        $this->assertEquals($email, $user->email());
        $this->assertEquals($balance, $user->balance());
    }
}
