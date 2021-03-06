<?php

declare(strict_types=1);

namespace MiniPay\Core\Transaction\Application;

use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\Validator\Constraints as Assert;

/** @psalm-immutable */
final class SendMoney
{
    public ?string $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    public string $payer;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    public string $payee;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("float")
     */
    public float $value;

    public function __construct(
        string $payer,
        string $payee,
        float $value
    ) {
        $this->id = Id::generate()->toString();

        $this->payer = $payer;
        $this->payee = $payee;
        $this->value = $value;
    }
}
