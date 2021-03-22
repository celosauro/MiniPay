<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\Id\Domain\Id;

use function array_splice;

/**
 * @ORM\Entity()
 *
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"default" = "DefaultUser", "storekeeper" = "StoreKeeperUser"})
 */
abstract class User
{
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

    /** @ORM\Embedded(class="Wallet") */
    protected Wallet $wallet;

    /** @var DomainEvent[] */
    protected array $domainEvents;

    /**
     * @psalm-param Id<User> $id
     */
    protected function __construct(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        Wallet $wallet
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->cpfOrCnpj = $cpfOrCnpj;
        $this->email = $email;
        $this->wallet = $wallet;

        $this->domainEvents = [];
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
        return $this->wallet->balance();
    }

    /**
     * @return DomainEvent[]
     */
    public function domainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }

    abstract public function type(): string;
}
