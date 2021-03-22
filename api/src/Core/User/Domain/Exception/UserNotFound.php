<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\ResourceNotFound;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class UserNotFound extends RuntimeException implements ResourceNotFound, Titled, Detailed
{
    private string $id;

    private string $cpfOrCnpj;

    private string $email;

    public static function withId(string $id): self
    {
        $exception = new self(
            sprintf('User not found with given ID %s.', $id)
        );
        $exception->id = $id;

        return $exception;
    }

    public static function withCpfOrCnpj(string $cpfOrCnpj): self
    {
        $exception = new self(
            sprintf('User not found with given CPF/CNPJ %s.', $cpfOrCnpj)
        );
        $exception->cpfOrCnpj = $cpfOrCnpj;

        return $exception;
    }

    public static function withEmail(string $email): self
    {
        $exception = new self(
            sprintf('User not found with given email %s.', $email)
        );
        $exception->email = $email;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'User not found';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    public function getExtraDetails(): array
    {
        if (empty($this->id) === false) {
            return ['ID' => $this->id];
        }

        if (empty($this->cpfOrCnpj) === false) {
            return ['CPF/CNPJ' => $this->cpfOrCnpj];
        }

        if (empty($this->email) === false) {
            return ['Email' => $this->email];
        }

        return [];
    }
}
