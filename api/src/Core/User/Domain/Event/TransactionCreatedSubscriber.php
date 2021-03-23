<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain\Event;

use MiniPay\Core\User\Application\TransactionNotificator;
use MiniPay\Framework\DomainEvent\Domain\DomainEvent;
use MiniPay\Framework\DomainEvent\Domain\DomainEventSubscriber;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;

class TransactionCreatedSubscriber implements DomainEventSubscriber
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
        return [TransactionCreated::class];
    }

    public function handle(DomainEvent $domainEvent): void
    {
        assert($domainEvent instanceof TransactionCreated);

        $command = new TransactionNotificator($domainEvent->payerId, $domainEvent->payeeId, $domainEvent->value);

        $this->bus->dispatch($command);
    }
}
