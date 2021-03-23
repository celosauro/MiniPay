<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure\Persistence;

use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Framework\Id\Domain\Id;

use function array_filter;
use function reset;

class InMemoryUserRepository implements UserRepository
{
    /** @var User[] */
    private array $items;

    /** @param User[] $items */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function save(User $user): void
    {
        $this->items[$user->id()->toString()] = $user;
    }

    /**
     * @psalm-param Id<User> $id
     */
    public function findOneByIdOrNull(Id $id): ?User
    {
        $user = array_filter($this->items, static function ($item) use ($id) {
            return $item->id()->isEqualTo($id);
        });

        if (empty($user)) {
            return null;
        }

        return reset($user);
    }

    public function findOneByCpfOrCnpjOrNull(string $cpfOrCnpj): ?User
    {
        $user = array_filter($this->items, static function ($item) use ($cpfOrCnpj) {
            return $item->cpfOrCnpj() === $cpfOrCnpj;
        });

        if (empty($user)) {
            return null;
        }

        return reset($user);
    }

    public function findOneByEmailOrNull(string $email): ?User
    {
        $user = array_filter($this->items, static function ($item) use ($email) {
            return $item->email() === $email;
        });

        if (empty($user)) {
            return null;
        }

        return reset($user);
    }
}
