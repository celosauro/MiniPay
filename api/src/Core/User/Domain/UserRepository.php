<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use MiniPay\Framework\Id\Domain\Id;

interface UserRepository
{
    /**
     * @psalm-param Id<User> $id
     */
    public function findOneByIdOrNull(Id $id): ?User;

    public function findOneByCpfOrCnpjOrNull(string $cpfOrCnpj): ?User;

    public function findOneByEmailOrNull(string $email): ?User;

    public function save(User $envelope): void;
}
