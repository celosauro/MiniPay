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
    private string $userId;
    private float $amount;

    public static function forTransactionReceveid(string $userId, float $amount): self
    {
        $exception = new self(sprintf(
            'Fail to send transaction notification to userId %s with amount %d.',
            $userId,
            $amount
        ));
        $exception->userId = $userId;
        $exception->amount = $amount;

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
            'userId' => $this->userId,
            'amount' => $this->amount,
        ];
    }
}
