<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Forbidden;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class UserAlreadyExists extends RuntimeException implements Forbidden, Titled, Detailed
{
    private string $cpfOrCnpj;

    private string $email;

    public static function withCpfOrCnpj(string $cpfOrCnpj): self
    {
        $exception = new self(
            sprintf('User already exists with given CPF/CNPJ %s.', $cpfOrCnpj)
        );
        $exception->cpfOrCnpj = $cpfOrCnpj;

        return $exception;
    }

    public static function withEmail(string $email): self
    {
        $exception = new self(
            sprintf('User already exists with given email %s.', $email)
        );
        $exception->email = $email;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'User already exists';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    public function getExtraDetails(): array
    {
        if (empty($this->cpfOrCnpj) === false) {
            return ['CPF/CNPJ' => $this->cpfOrCnpj];
        }

        if (empty($this->email) === false) {
            return ['Email' => $this->email];
        }

        return [];
    }
}
