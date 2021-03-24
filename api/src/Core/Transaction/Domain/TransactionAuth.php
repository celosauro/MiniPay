<?php

declare(strict_types=1);

namespace MiniPay\Core\Transaction\Domain;

interface TransactionAuth
{
    public function auth(): bool;
}
