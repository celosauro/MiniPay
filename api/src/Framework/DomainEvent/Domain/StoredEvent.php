<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StoredEvent implements DomainEvent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /** @ORM\Column(type="text") */
    private string $body;

    /** @ORM\Column(type="datetime_immutable") */
    private DateTimeImmutable $occurredOn;

    /** @ORM\Column(type="string") */
    private string $typeName;

    public function __construct(string $typeName, DateTimeImmutable $occurredOn, string $body)
    {
        $this->typeName = $typeName;
        $this->occurredOn = $occurredOn;
        $this->body = $body;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function typeName(): string
    {
        return $this->typeName;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
