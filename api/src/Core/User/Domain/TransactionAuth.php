<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

interface TransactionAuth
{
    public function auth(): bool;
}
