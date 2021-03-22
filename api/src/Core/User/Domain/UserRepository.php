<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use MiniPay\Framework\Id\Domain\Id;

interface UserRepository
{
    /**
     * @psalm-param Id<User> $id
     */
    public function findOneById(Id $id) : User;

    public function save(User $envelope) : void;
}
