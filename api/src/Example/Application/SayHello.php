<?php

declare(strict_types=1);

namespace MiniPay\Example\Application;

use Symfony\Component\Validator\Constraints as Assert;

/** @psalm-immutable */
final class SayHello
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=100)
     */
    public ?string $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=20, max=200)
     */
    public ?string $message;

    public function __construct(
        ?string $name = null,
        ?string $message = null
    ) {
        $this->name = $name;
        $this->message = $message;
    }

    public function withName(string $name): self
    {
        return new self($name, $this->message);
    }
}
