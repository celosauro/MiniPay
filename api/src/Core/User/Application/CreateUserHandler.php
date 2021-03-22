<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Application;

use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\Exception\CannotCreateUser;
use MiniPay\Core\User\Domain\Exception\UserAlreadyExists;
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

    public function __invoke(CreateUser $command): void
    {
        $this->throwExceptionIfUserTypeIsInvalid($command->type);

        $this->throwExceptionIfUserAlreadyExistsWithCpfOrCnpj($command->cpfOrCnpj);
        $this->throwExceptionIfUserAlreadyExistsWithEmail($command->email);

        $user = null;
        if ($command->type === DefaultUser::USER_TYPE) {
            $user = $this->createDefaultUser($command);
        }

        if ($command->type === StoreKeeperUser::USER_TYPE) {
            $user = $this->createStorekeeperUser($command);
        }

        assert($user instanceof User);

        $this->repository->save($user);
    }

    private function throwExceptionIfUserTypeIsInvalid(string $type): void
    {
        if (in_array($type, [DefaultUser::USER_TYPE, StoreKeeperUser::USER_TYPE]) === false) {
            throw CannotCreateUser::WithType($type);
        }
    }

    private function throwExceptionIfUserAlreadyExistsWithCpfOrCnpj(string $cpfOrCnpj): void
    {
        $foundUserByCpfOrCnpj = $this->repository->findOneByCpfOrCnpjOrNull($cpfOrCnpj);

        if ($foundUserByCpfOrCnpj instanceof User) {
            throw UserAlreadyExists::withCpfOrCnpj($cpfOrCnpj);
        }
    }

    private function throwExceptionIfUserAlreadyExistsWithEmail(string $email): void
    {
        $foundUserByEmail = $this->repository->findOneByEmailOrNull($email);

        if ($foundUserByEmail instanceof User) {
            throw UserAlreadyExists::withEmail($email);
        }
    }

    private function createDefaultUser(CreateUser $command): User
    {
        return User::create(
            Id::fromString($command->id ?? ''),
            $command->fullName,
            $command->cpfOrCnpj,
            $command->email,
            new Wallet($command->walletAmount)
        );
    }

    private function createStorekeeperUser(CreateUser $command): User
    {
        return StoreKeeperUser::create(
            Id::fromString($command->id ?? ''),
            $command->fullName,
            $command->cpfOrCnpj,
            $command->email,
            new Wallet($command->walletAmount)
        );
    }
}
