<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

interface TransactionReceivedNotificator
{
    public function send(string $userId, float $amount): void;
}
