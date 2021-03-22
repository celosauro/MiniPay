<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Domain;

use MiniPay\Core\User\Domain\Exception\InsufficientBalance;
use MiniPay\Core\User\Domain\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnAccountWithBalance(): void
    {
        $initialAmount = 100.00;

        $account = new Wallet($initialAmount);

        $this->assertEquals($initialAmount, $account->balance());
    }

    /**
     * @test
     */
    public function shouldWithdrawMoneyWhenHasBalance(): void
    {
        $initialAmount = 100.00;
        $amountToWithdraw = 90.00;
        $expectedBalance = 10.00;

        $account = new Wallet($initialAmount);

        $account->withdraw($amountToWithdraw);

        $this->assertEquals($expectedBalance, $account->balance());
    }

    /**
     * @test
     */
    public function shouldThrowInsuficientBalanceErrorWhenWithdrawWithoutEnoughBalance(): void
    {
        $this->expectException(InsufficientBalance::class);

        $initialAmount = 10.00;
        $amountToWithdraw = 50.00;

        $account = new Wallet($initialAmount);

        $account->withdraw($amountToWithdraw);
    }

    /**
     * @test
     */
    public function shouldReceiveMoney(): void
    {
        $initialAmount = 100.00;
        $amountToReceive = 90.00;
        $expectedBalance = 190.00;

        $account = new Wallet($initialAmount);

        $account->receive($amountToReceive);

        $this->assertEquals($expectedBalance, $account->balance());
    }
}
