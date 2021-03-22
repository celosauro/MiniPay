<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use MiniPay\Core\User\Domain\Event\DomainEvent;
use MiniPay\Framework\Id\Domain\Id;

use function array_splice;

/**
 * @ORM\Entity()
 *
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"default" = "User", "storekeeper" = "StoreKeeper"})
 */
class User
{
    protected const USER_TYPE = 'default';

    /**
     * @ORM\Id
     * @ORM\Column(type="app_id", name="id")
     *
     * @psalm-var Id<User> $id
     */
    protected Id $id;

    /** @ORM\Column(type="string", length=14, unique=true) */
    protected string $cpfOrCnpj;

    /** @ORM\Column(type="string") */
    protected string $fullName;

    /** @ORM\Column(type="string", unique=true) */
    protected string $email;

    /** @ORM\Embedded(class="Account") */
    protected Account $account;

    /** @var DomainEvent[] */
    protected array $domainEvents;

    /**
     * @psalm-param Id<User> $id
     */
    private function __construct(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        Account $account
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->cpfOrCnpj = $cpfOrCnpj;
        $this->email = $email;
        $this->account = $account;

        $this->domainEvents = [];
    }

    /**
     * @psalm-param Id<User> $id
     */
    public static function create(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        Account $account
    ): self {
        return new self(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $account
        );
    }

    /**
     * @psalm-return Id<User> $id
     */
    public function id(): Id
    {
        return $this->id;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function cpfOrCnpj(): string
    {
        return $this->cpfOrCnpj;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function balance(): float
    {
        return $this->account->balance();
    }

    /**
     * @return DomainEvent[]
     */
    public function domainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }
}
