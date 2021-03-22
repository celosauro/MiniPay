<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Infrastructure\Persistence;

use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\StoreKeeperUser;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Core\User\Infrastructure\Persistence\DoctrineUserRepository;
use MiniPay\Framework\DomainEvent\Domain\DomainEventPublisher;
use MiniPay\Framework\DomainEvent\Domain\PersistDomainEventSubscriber;
use MiniPay\Framework\DomainEvent\Infrastructure\InMemoryEventStore;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Tests\Framework\DoctrineTestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use function assert;

class DoctrineUserRepositoryTest extends DoctrineTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $subscriber = new PersistDomainEventSubscriber($eventStore);
        $eventBus = new MessageBus();
        $publisher = new DomainEventPublisher([$subscriber], $eventBus);

        $this->repository = new DoctrineUserRepository($this->entityManager, $publisher);
    }

    /**
     * @test
     */
    public function shouldSaveADefaultUserInRepository(): void
    {
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $this->assertFalse($this->entityManager->contains($user));

        $this->repository->save($user);
        $this->entityManager->flush();

        $foundUser = $this->repository->findOneByIdOrNull($id);
        assert($foundUser instanceof User);

        $this->assertEquals($foundUser, $user);
        $this->assertEquals($user::USER_TYPE, $foundUser->type());
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertTrue($this->entityManager->contains($user));
        $this->assertEmpty($foundUser->domainEvents());
    }

    /**
     * @test
     */
    public function shouldSaveAStorekeeperUserInRepository(): void
    {
        $id = Id::generate();
        $user = $this->createStorekeeperUser($id);

        $this->assertFalse($this->entityManager->contains($user));

        $this->repository->save($user);
        $this->entityManager->flush();

        $foundUser = $this->repository->findOneByIdOrNull($id);
        assert($foundUser instanceof User);

        $this->assertEquals($foundUser, $user);
        $this->assertEquals($user::USER_TYPE, $foundUser->type());
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertTrue($this->entityManager->contains($user));
        $this->assertEmpty($foundUser->domainEvents());
    }

    /**
     * @test
     */
    public function shouldFindUserByCpfOrCnpj(): void
    {
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $this->assertFalse($this->entityManager->contains($user));

        $this->repository->save($user);
        $this->entityManager->flush();

        $foundUser = $this->repository->findOneByCpfOrCnpjOrNull($user->cpfOrCnpj());
        assert($foundUser instanceof User);

        $this->assertEquals($foundUser, $user);
    }

    /**
     * @test
     */
    public function shouldFindUserByEmail(): void
    {
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $this->assertFalse($this->entityManager->contains($user));

        $this->repository->save($user);
        $this->entityManager->flush();

        $foundUser = $this->repository->findOneByEmailOrNull($user->email());
        assert($foundUser instanceof User);

        $this->assertEquals($foundUser, $user);
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createDefaultUser(Id $id): DefaultUser
    {
        return DefaultUser::create(
            $id,
            'Foo Bar',
            '88498957044',
            'foo@bar.com',
            new Wallet(99.9)
        );
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createStorekeeperUser(Id $id): StoreKeeperUser
    {
        return StoreKeeperUser::create(
            $id,
            'Foo Bar',
            '88498957044',
            'foo@bar.com',
            new Wallet(99.9)
        );
    }
}
