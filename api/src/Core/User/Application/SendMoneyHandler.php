<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Core\User\Domain\Exception\CannotSendMoney;
use MiniPay\Core\User\Domain\Exception\TransactionUnauthorized;
use MiniPay\Core\User\Domain\Exception\UserNotFound;
use MiniPay\Core\User\Domain\StoreKeeperUser;
use MiniPay\Core\User\Domain\TransactionAuth;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Framework\DomainEvent\Domain\EventStore;
use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;

class SendMoneyHandler implements MessageHandlerInterface
{
    private MessageBusInterface $eventBus;

    private UserRepository $repository;

    private EventStore $eventStore;

    private TransactionAuth $transactionAuthClient;

    public function __construct(
        MessageBusInterface $eventBus,
        EventStore $eventStore,
        UserRepository $repository,
        TransactionAuth $transactionAuthClient
    ) {
        $this->eventBus = $eventBus;
        $this->eventStore = $eventStore;
        $this->repository = $repository;
        $this->transactionAuthClient = $transactionAuthClient;
    }

    public function __invoke(SendMoney $command): void
    {
        $payer = $this->repository->findOneByIdOrNull(Id::fromString($command->payer));
        $this->throwExceptionIfUserNotFound($payer, $command->payer);
        $this->throwExceptionIfPayerIsStoreKeeperUser($payer);

        $payee = $this->repository->findOneByIdOrNull(Id::fromString($command->payee));
        $this->throwExceptionIfUserNotFound($payee, $command->payee);

        assert($payer instanceof User);
        assert($payee instanceof User);

        $this->throwExceptionIfTransactionIsNotAuthorized($command->payer, $command->payee, $command->value);

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

    private function throwExceptionIfPayerIsStoreKeeperUser(?User $payer): void
    {
        if ($payer instanceof StoreKeeperUser) {
            throw CannotSendMoney::fromStoreKeeperUser($payer->id()->toString());
        }
    }

    private function throwExceptionIfTransactionIsNotAuthorized(string $payerId, string $payeeId, float $value): void
    {
        if ($this->transactionAuthClient->auth() === false) {
            throw TransactionUnauthorized::withData($payerId, $payeeId, $value);
        }
    }
}
