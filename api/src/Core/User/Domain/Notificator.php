<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

interface Notificator
{
    public function send(string $userId, float $amount): void;
}
