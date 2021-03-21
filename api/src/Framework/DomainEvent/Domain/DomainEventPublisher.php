<?php

declare(strict_types=1);

namespace MiniPay\Framework\DomainEvent\Domain;

use Symfony\Component\Messenger\MessageBusInterface;

class DomainEventPublisher
{
    /** @var DomainEventSubscriber[] */
    private array $subscribers;
    private MessageBusInterface $eventBus;

    /** @param DomainEventSubscriber[] $subscribers*/
    public function __construct(array $subscribers, MessageBusInterface $eventBus)
    {
        $this->subscribers = $subscribers;
        $this->eventBus = $eventBus;
    }

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }

    public function publish(DomainEvent $event): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($event);
            $this->eventBus->dispatch($event);
        }
    }
}
