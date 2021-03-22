<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateUserHandler implements MessageHandlerInterface
{
    private MessageBusInterface $eventBus;

    private UserRepository $repository;

    private EventStore $eventStore;

    public function __construct(
        MessageBusInterface $eventBus,
        EventStore $eventStore,
        UserRepository $repository
    ) {
        $this->eventBus = $eventBus;
        $this->eventStore = $eventStore;
        $this->repository = $repository;
    }

    public function __invoke(CreateUser $command) : void
    {
        $user = User::create(
            Id::fromString($command->id),
            $command->fullName,
            $command->cpfOrCnpj,
            $command->email,
            new Wallet($command->walletAmount)
        );

        $this->repository->save($user);

//        $evento = PagadorFoiAtivado::criar($pagador->id()->toString());
//
//        $this->eventStore->append($evento);
//
//        $this->eventBus->dispatch($evento);
    }
}
