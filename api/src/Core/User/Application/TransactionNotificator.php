<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use Symfony\Component\Validator\Constraints as Assert;

/** @psalm-immutable */
final class TransactionNotificator
{
    /** @Assert\NotBlank() */
    public string $payerId;

    /** @Assert\NotBlank() */
    public string $payeeId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("float")
     */
    public float $value;

    public function __construct(
        string $payerId,
        string $payeeId,
        float $value
    ) {
        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
        $this->value = $value;
    }
}
