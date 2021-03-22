<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use DateTimeImmutable;
use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\Event\UserCreated;
use MiniPay\Core\User\Domain\Exception\CannotCreateUser;
use MiniPay\Core\User\Domain\Exception\CannotSendMoney;
use MiniPay\Core\User\Domain\Exception\UserAlreadyExists;
use MiniPay\Core\User\Domain\Exception\UserNotFound;
use MiniPay\Core\User\Domain\StoreKeeperUser;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;
use function in_array;

class SendMoneyHandler implements MessageHandlerInterface
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

    public function __invoke(SendMoney $command): void
    {
        $payer = $this->repository->findOneByIdOrNull(Id::fromString($command->payer));
        $this->throwExceptionIfUserNotFound($payer, $command->payer);
        $this->throwExceptionIfPayerIsStoreKeeperUser($payer);

        $payee = $this->repository->findOneByIdOrNull(Id::fromString($command->payee));
        $this->throwExceptionIfUserNotFound($payee, $command->payee);

        $payer->withdraw($command->value);
        $payee->receive($command->value);

        $this->repository->save($payer);
        $this->repository->save($payee);

//        $this->dispatchUserCreatedEvent($user->id()->toString());
    }

    private function throwExceptionIfUserNotFound(?User $payer, string $userId): void
    {
        if ($payer === null) {
            throw UserNotFound::withId($userId);
        }
    }

    private function throwExceptionIfPayerIsStoreKeeperUser(User $payer): void
    {
        if ($payer instanceOf StoreKeeperUser) {
            throw CannotSendMoney::fromStoreKeeperUser($payer->id()->toString());
        }
    }

//    private function dispatchUserCreatedEvent(string $userId): void
//    {
//        $event = UserCreated::create($userId, new DateTimeImmutable());
//
//        $this->eventStore->append($event);
//
//        $this->eventBus->dispatch($event);
//    }
}
