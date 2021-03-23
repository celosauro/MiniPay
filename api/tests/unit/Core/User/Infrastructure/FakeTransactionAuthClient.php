<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Infrastructure;

use MiniPay\Core\User\Domain\TransactionAuth;

class FakeTransactionAuthClient implements TransactionAuth
{
    private bool $auth;

    public function __construct(bool $auth = true)
    {
        $this->auth = $auth;
    }

    public function auth(): bool
    {
        return $this->auth;
    }
}
