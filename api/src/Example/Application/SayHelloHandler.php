<?php

declare(strict_types=1);

namespace MiniPay\Example\Application;

use Doctrine\Persistence\ObjectManager;
use MiniPay\Example\Domain\Message;
use MiniPay\Framework\Id\Domain\Id;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SayHelloHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ObjectManager $objectManager;

    public function __construct(
        LoggerInterface $logger,
        ObjectManager $objectManager
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }

    public function __invoke(SayHello $command): void
    {
        $this->logger->info(
            'Hello {name}! Your message: {message}',
            [
                'name' => $command->name,
                'message' => $command->message,
            ]
        );
        $message = Message::create(
            Id::generate(),
            $command->message ?? '',
            $command->name ?? ''
        );
        $this->objectManager->persist($message);
    }
}
