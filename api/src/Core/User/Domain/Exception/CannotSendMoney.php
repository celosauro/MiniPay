<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Exception;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Forbidden;
use Lcobucci\ErrorHandling\Problem\InvalidRequest;
use Lcobucci\ErrorHandling\Problem\Titled;
use RuntimeException;

use function sprintf;

final class CannotSendMoney extends RuntimeException implements Forbidden, Titled, Detailed
{
    private string $userId;

    public static function fromStoreKeeperUser(string $userId): self
    {
        $exception = new self(
            sprintf('Cannot send money from StoreKeeperUser userId %s.', $userId)
        );
        $exception->userId = $userId;

        return $exception;
    }

    public function getTitle(): string
    {
        return 'Cannot send money';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    public function getExtraDetails(): array
    {
        return [
            'User ID' => $this->userId,
        ];
    }
}
