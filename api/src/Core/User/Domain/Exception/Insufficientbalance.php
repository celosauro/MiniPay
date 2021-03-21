<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Forbidden;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class Insufficientbalance extends RuntimeException implements Forbidden, Titled, Detailed
{
    private float $currentBalance;

    public static function forWithdraw(float $currentBalance, float $amountToWithdraw): self
    {
        $exception = new self(
            sprintf('Your current balance is %d, withdraw request is %d.', $currentBalance, $amountToWithdraw)
        );
        $exception->currentBalance = $currentBalance;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'You do not have enough balance.';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, int>
     */
    public function getExtraDetails(): array
    {
        return ['balance' => $this->currentBalance];
    }
}
