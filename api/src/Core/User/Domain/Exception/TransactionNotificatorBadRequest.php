<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Exception;
use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\InvalidRequest;
use Lcobucci\ErrorHandling\Problem\Titled;

use function sprintf;

final class TransactionNotificatorBadRequest extends Exception implements InvalidRequest, Titled, Detailed
{
    private string $payerId;
    private string $payeeId;
    private float $value;

    public static function forTransactionReceveid(string $payerId, string $payeeId, float $value): self
    {
        $exception = new self(sprintf(
            'Fail to send transaction notification to payeeId %s from payerId %s with value %d.',
            $payeeId,
            $payerId,
            $value
        ));
        $exception->payerId = $payerId;
        $exception->payeeId = $payeeId;
        $exception->value = $value;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'Invalid request';
    }

    /**
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
