<?php

declare(strict_types=1);

namespace MiniPay\Example\Domain;

use Doctrine\ORM\Mapping as ORM;
use MiniPay\Framework\Id\Domain\Id;

/**
 * @ORM\Entity()
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="mensageiro_id", name="id")
     *
     * @psalm-var Id<Message> $id
     */
    private Id $id;

    /** @ORM\Column(type="string", length=255) */
    private string $message;

    /** @ORM\Column(type="string", length=255) */
    private string $authorName;

    /**
     * @psalm-param Id<Message> $id
     * */
    private function __construct(
        Id $id,
        string $message,
        string $authorName
    ) {
        $this->id = $id;
        $this->message = $message;
        $this->authorName = $authorName;
    }

    /**
     * @psalm-param Id<Message> $id
     * */
    public static function create(
        Id $id,
        string $message,
        string $authorName
    ): self {
        return new self(
            $id,
            $message,
            $authorName
        );
    }

    /**
     * @return Id<Message>
     */
    public function id(): Id
    {
        return $this->id;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function authorName(): string
    {
        return $this->authorName;
    }
}
