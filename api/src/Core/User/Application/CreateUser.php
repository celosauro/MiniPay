<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/** @psalm-immutable */
final class CreateUser
{
    public ?string $id;
    /**
     * @SerializedName("cpf_cnpj")
     * @Assert\NotBlank()
     * @Assert\Length(min="11", max="14")
     */
    public string $cpfOrCnpj;

    /**
     * @SerializedName("full_name")
     * @Assert\NotBlank()
     * @Assert\Length(min="5", max="50")
     */
    public string $fullName;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public string $email;

    /**
     * @SerializedName("wallet_amount")
     * @Assert\Type("float")
     */
    public float $walletAmount;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"default", "storekeeper"})
     */
    public string $type;

    public function __construct(
        string $cpfOrCnpj,
        string $fullName,
        string $email,
        float $walletAmount,
        string $type
    ) {
        $this->id = Id::generate()->toString();

        $this->cpfOrCnpj = $cpfOrCnpj;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->walletAmount = $walletAmount;
        $this->type = $type;
    }
}
