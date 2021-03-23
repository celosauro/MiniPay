<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Forbidden;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class TransactionUnauthorized extends RuntimeException implements Forbidden, Titled, Detailed
{
    private string $payerId;
    private string $payeeId;
    private float $value;

    public static function withData(string $payerId, string $payeeId, float $value): self
    {
        $exception = new self(
            sprintf(
                'Transaction Unauthorized from payerId %s to payeeId %s with value %d.',
                $payerId,
                $payeeId,
                $value
            )
        );
        $exception->payerId = $payerId;
        $exception->payeeId = $payeeId;
        $exception->value = $value;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'Transaction Unauthorized.';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string|float>
     */
    public function getExtraDetails(): array
    {
        return [
            'payerId' => $this->payerId,
            'payeeId' => $this->payeeId,
            'value' => $this->value,
        ];
    }
}
