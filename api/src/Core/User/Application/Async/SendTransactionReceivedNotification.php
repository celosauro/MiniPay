<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application\Async;

use Symfony\Component\Validator\Constraints as Assert;

/** @psalm-immutable */
final class SendTransactionReceivedNotification
{
    /** @Assert\NotBlank() */
    public string $userId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("float")
     */
    public float $amount;

    public function __construct(
        string $userId,
        float $amount
    ) {
        $this->userId = $userId;
        $this->amount = $amount;
    }
}
