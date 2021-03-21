<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use MiniPay\Core\User\Domain\Event\DomainEvent;
use MiniPay\Framework\Id\Domain\Id;
use function array_merge;
use function array_splice;
use function array_udiff;
use function assert;
use function iterator_to_array;
use function sprintf;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="app_id", name="id")
     *
     * @psalm-var Id<User> $id
     */
    private Id $id;

    /** @ORM\Column(type="string", length=14, unique=true) */
    private string $cpfOrCnpj;

    /** @ORM\Column(type="string") */
    private string $fullName;

    /** @ORM\Column(type="string", unique=true) */
    private string $email;

    /** @ORM\Embedded(class="Account") */
    private ?Account $account;

    /** @var DomainEvent[] */
    private array $domainEvents;

    /**
     * @psalm-param Id<User> $id
     */
    private function __construct(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        float $amount
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->cpfOrCnpj = $cpfOrCnpj;
        $this->email = $email;
        $this->domainEvents = [];

        $this->account = new Account($amount);
    }

    /**
     * @psalm-param Id<User> $id
     */
    public static function create(
        Id $id,
        string $fullName,
        string $cpfOrCnpj,
        string $email,
        float $amount
    ) : self {
        return new self(
            $id,
            $fullName,
            $cpfOrCnpj,
            $email,
            $amount
        );
    }

    /**
     * @psalm-return Id<User> $id
     */
    public function id() : Id
    {
        return $this->id;
    }

    public function fullName() : string
    {
        return $this->fullName;
    }

    public function cpfOrCnpj() : string
    {
        return $this->cpfOrCnpj;
    }

    public function email() : string
    {
        return $this->email;
    }

    /**
     * @return DomainEvent[]
     */
    public function domainEvents() : array
    {
        return array_splice($this->domainEvents, 0);
    }

}
