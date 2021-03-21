<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Domain;

use MiniPay\Core\User\Domain\Account;
use MiniPay\Core\User\Domain\Exception\Insufficientbalance;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnAccountWithBalance(): void
    {
        $initialAmount = 100.00;

        $account = new Account($initialAmount);

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

        $account = new Account($initialAmount);

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

        $account = new Account($initialAmount);

        $account->withdraw($amountToWithdraw);
    }
}
