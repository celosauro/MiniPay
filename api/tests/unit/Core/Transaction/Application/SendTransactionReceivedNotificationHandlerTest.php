<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\Transaction\Application;

use MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification;
use MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotificationHandler;
use MiniPay\Core\Transaction\Domain\TransactionReceivedNotificator;
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
