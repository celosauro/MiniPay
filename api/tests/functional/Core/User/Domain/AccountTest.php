<?php

namespace MiniPay\Tests\Core\User\Domain;

use MiniPay\Core\User\Domain\Account;
use MiniPay\Core\User\Domain\User;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnAccountWithBalance() : void
    {
        $amount = '100.00';

        $account = new Account($amount);

        $this->assertEquals($amount, $account->balance());
    }

    /**
     * @test
     */
    public function shouldWithdrawMoneyWhenHasBalance() : void
    {
        $amount = '100.00';
        $amountToWithdraw = '90.00';
        $expectedBalance = '10.00';

        $account = new Account($amount);

        $account->withdraw($amountToWithdraw);

        $this->assertEquals($expectedBalance, $account->balance());
    }
}
