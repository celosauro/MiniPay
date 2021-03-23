<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Domain;

use DateTimeImmutable;
use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\Event\TransactionReceived;
use MiniPay\Core\User\Domain\Event\TransactionWithdrew;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

class DefaultUserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnUser(): void
    {
        $id = Id::fromString('user-id');
        $fullName = 'Foo Bar';
        $cpfOrCnpjCleaned = '88498957044';
        $cpfOrCnpj = '884.989.570-44';
        $email = 'foobar@test.com';
        $balance = 99.99;
        $account = new Wallet($balance);

        $user = DefaultUser::create(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $account
        );

        $this->assertTrue($id->isEqualTo($user->id()));
        $this->assertEquals($fullName, $user->fullName());
        $this->assertEquals($cpfOrCnpjCleaned, $user->cpfOrCnpj());
        $this->assertEquals($email, $user->email());
        $this->assertEquals($balance, $user->balance());
        $this->assertEquals($user::USER_TYPE, $user->type());
    }

    /**
     * @test
     */
    public function shouldWithdrawAmountSuccessfully(): void
    {
        $id = Id::fromString('user-id');
        $fullName = 'Foo Bar';
        $cpfOrCnpj = '884.989.570-44';
        $email = 'foobar@test.com';
        $balance = 100.0;
        $account = new Wallet($balance);

        $amountToWithdraw = 50.0;
        $exceptedBalanceAfterWithdraw = 50.0;

        $expectedEvent = TransactionWithdrew::create($id->toString(), $amountToWithdraw, new DateTimeImmutable());

        $user = DefaultUser::create(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $account
        );

        $user->withdraw($amountToWithdraw);

        $events = $user->domainEvents();

        $this->assertEquals($exceptedBalanceAfterWithdraw, $user->balance());
        $this->assertCount(1, $events);
        $this->assertEquals($id->toString(), $expectedEvent->userId);
        $this->assertEquals($amountToWithdraw, $expectedEvent->amount);
    }

    /**
     * @test
     */
    public function shouldReceiveAmountSuccessfully(): void
    {
        $id = Id::fromString('user-id');
        $fullName = 'Foo Bar';
        $cpfOrCnpj = '884.989.570-44';
        $email = 'foobar@test.com';
        $balance = 100.0;
        $account = new Wallet($balance);

        $amountToReceive = 50.0;
        $exceptedBalanceAfterReceive = 150.0;

        $expectedEvent = TransactionReceived::create($id->toString(), $amountToReceive, new DateTimeImmutable());

        $user = DefaultUser::create(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $account
        );

        $user->receive($amountToReceive);

        $events = $user->domainEvents();

        $this->assertEquals($exceptedBalanceAfterReceive, $user->balance());
        $this->assertCount(1, $events);
        $this->assertEquals($id->toString(), $expectedEvent->userId);
        $this->assertEquals($amountToReceive, $expectedEvent->amount);
    }
}
