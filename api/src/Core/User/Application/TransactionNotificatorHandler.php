<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Core\User\Domain\Notificator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TransactionNotificatorHandler implements MessageHandlerInterface
{
    private Notificator $notificator;

    public function __construct(Notificator $notificator)
    {
        $this->notificator = $notificator;
    }

    public function __invoke(TransactionNotificator $command): void
    {
        $this->notificator->send($command->userId, $command->amount);
    }
}
