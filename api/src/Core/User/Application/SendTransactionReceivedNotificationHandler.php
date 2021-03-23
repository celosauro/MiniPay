<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Core\User\Domain\TransactionReceivedNotificator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendTransactionReceivedNotificationHandler implements MessageHandlerInterface
{
    private TransactionReceivedNotificator $notificator;

    public function __construct(TransactionReceivedNotificator $notificator)
    {
        $this->notificator = $notificator;
    }

    public function __invoke(SendTransactionReceivedNotification $command): void
    {
        $this->notificator->send($command->userId, $command->amount);
    }
}
