<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Event;

use MiniPay\Core\User\Application\TransactionNotificator;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Domain\DomainEventSubscriber;
use Symfony\Component\Messenger\MessageBusInterface;

class TransactionReceivedSubscriber implements DomainEventSubscriber
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @return array<int, string>
     */
    public function subscribedEvents(): array
    {
        return [TransactionReceived::class];
    }

    public function handle(DomainEvent $domainEvent): void
    {
        if (! ($domainEvent instanceof TransactionReceived)) {
            return;
        }

        $command = new TransactionNotificator($domainEvent->userId, $domainEvent->amount);

        $this->bus->dispatch($command);
    }
}
