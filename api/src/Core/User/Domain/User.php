<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use MiniPay\Core\User\Domain\Event\TransactionReceived;
use MiniPay\Core\User\Domain\Event\TransactionWithdrew;
use MiniPay\Core\User\Domain\Event\UserCreated;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\Id\Domain\Id;

use function array_splice;
use function base64_encode;
use function preg_replace;

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
    protected array $domainEvents = [];

    /** @ORM\Column(type="string", length=255) */
    protected string $salt;

    /** @ORM\Column(type="string", length=255) */
    protected string $token;

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
        $this->cpfOrCnpj = $this->cleanNonDigitCharacter($cpfOrCnpj);
        $this->email = $email;
        $this->wallet = $wallet;

        $this->salt = $this->generateHashString();
        $this->token = $this->generateHashString();

        $this->domainEvents[] = UserCreated::create(
            $this->id()->toString(),
            new DateTimeImmutable()
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
        return $this->wallet->balance();
    }

    public function withdraw(float $value): void
    {
        $this->wallet->withdraw($value);

        $this->domainEvents[] = TransactionWithdrew::create(
            $this->id()->toString(),
            $value,
            new DateTimeImmutable()
        );
    }

    public function receive(float $value): void
    {
        $this->wallet->receive($value);

        $this->domainEvents[] = TransactionReceived::create(
            $this->id()->toString(),
            $value,
            new DateTimeImmutable()
        );
    }

    /**
     * @return DomainEvent[]
     */
    public function domainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }

    abstract public function type(): string;

    public function checkSecret(string $secret): bool
    {
        return SecretEncoder::fromTokenAndSalt($this->token, $this->salt)->check($secret);
    }

    public function createSecret(): string
    {
        return SecretEncoder::fromTokenAndSalt($this->token, $this->salt)->generate();
    }

    private function generateHashString(): string
    {
        return base64_encode((string) Id::generate());
    }

    private function cleanNonDigitCharacter(string $string): string
    {
        return preg_replace('/[^0-9]/', '', $string) ?? '';
    }
}
