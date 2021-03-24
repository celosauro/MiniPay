<?php

declare(strict_types=1);

namespace MiniPay\Core\Transaction\Domain;

interface TransactionReceivedNotificator
{
    public function send(string $userId, float $amount): void;
}
