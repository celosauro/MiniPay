<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Application;

use MiniPay\Core\User\Application\SendTransactionReceivedNotification;
use MiniPay\Core\User\Application\SendTransactionReceivedNotificationHandler;
use MiniPay\Core\User\Domain\TransactionReceivedNotificator;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

use function assert;

class SendTransactionReceivedNotificationHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSendNotification(): void
    {
        $notificator = $this->getMockBuilder(TransactionReceivedNotificator::class)->getMock();
        $notificator->expects($this->once())->method('send');

        assert($notificator instanceof TransactionReceivedNotificator);

        $handler = new SendTransactionReceivedNotificationHandler($notificator);

        $userId = Id::fromString('user-id')->toString();
        $amount = 100.0;
        $command = new SendTransactionReceivedNotification($userId, $amount);

        $handler($command);
    }
}
