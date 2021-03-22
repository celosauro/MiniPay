<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure\Persistence;

use Doctrine\Persistence\ObjectManager;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Framework\DomainEvent\Domain\DomainEventPublisher;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Core\User\Domain\Exception\UserNotFound;

class DoctrineUserRepository implements UserRepository
{
    private const ENTITY = User::class;

    private ObjectManager $objectManager;

    private DomainEventPublisher $publisher;

    public function __construct(ObjectManager $objectManager, DomainEventPublisher $publisher)
    {
        $this->objectManager = $objectManager;
        $this->publisher = $publisher;
    }

    public function save(User $user) : void
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        foreach ($user->domainEvents() as $event) {
            $this->publisher->publish($event);
        }
    }

    /**
     * @psalm-param Id<User> $id
     */
    public function findOneByIdOrNull(Id $id) : User
    {
        $user = $this->objectManager->getRepository(self::ENTITY)->findOneBy([
            'id' => $id,
        ]);

        if ($user instanceof User) {
            return $user;
        }

        throw UserNotFound::withId($id->toString());
    }

    public function findOneByCpfOrCnpjOrNull(string $cpfOrCnpj): ?User
    {
        // TODO: Implement findOneByCpfOrCnpjOrNull() method.
    }

    public function findOneByEmailOrNull(string $email): ?User
    {
        // TODO: Implement findOneByEmailOrNull() method.
    }
}
