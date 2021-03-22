<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use Doctrine\ORM\Mapping as ORM;
use MiniPay\Framework\Id\Domain\Id;

/**
 * @ORM\Entity()
 */
class DefaultUser extends User
{
    public const USER_TYPE = 'default';

    /**
     * @psalm-param Id<User> $id
     */
    public static function create(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        Wallet $wallet
    ): self {
        return new self(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $wallet
        );
    }

    public function type(): string
    {
        return self::USER_TYPE;
    }
}
