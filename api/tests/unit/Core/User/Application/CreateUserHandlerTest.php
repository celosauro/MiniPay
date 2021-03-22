<?php

namespace MiniPay\Tests\Core\User\Application;

use MiniPay\Core\User\Application\CreateUser;
use MiniPay\Core\User\Application\CreateUserHandler;
use MiniPay\Core\User\Domain\Exception\CannotCreateUser;
use MiniPay\Core\User\Domain\Exception\UserAlreadyExists;
use MiniPay\Core\User\Infrastructure\Persistence\DoctrineUserRepository;
use MiniPay\Core\User\Infrastructure\Persistence\InMemoryUserRepository;
use MiniPay\Framework\DomainEvent\Domain\DomainEventPublisher;
use MiniPay\Framework\DomainEvent\Domain\PersistDomainEventSubscriber;
use MiniPay\Framework\DomainEvent\Infrastructure\InMemoryEventStore;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Tests\Framework\DoctrineTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CreateUserHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnUser() : void
    {
        $repository = new InMemoryUserRepository();
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $handler = new CreateUserHandler($eventBus, $eventStore, $repository);

        $cpfOrCnpj = '01257534033';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $type = 'default';
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount,
            $type
        );

        $handler($command);

        $createdUser = $repository->findOneByIdOrNull(Id::fromString($command->id));

        $this->assertNotNull($createdUser);
        $this->assertEquals($command->cpfOrCnpj, $createdUser->cpfOrCnpj());
        $this->assertEquals($command->fullName, $createdUser->fullName());
        $this->assertEquals($command->email, $createdUser->email());
        $this->assertEquals($command->walletAmount, $createdUser->balance());
    }

    /**
     * @test
     */
    public function shouldThrowCannotCreateUserWithInvalidType() : void
    {
        $this->expectException(CannotCreateUser::class);

        $repository = new InMemoryUserRepository();
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $handler = new CreateUserHandler($eventBus, $eventStore, $repository);

        $cpfOrCnpj = '01257534033';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $type = 'invalid-type';
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount,
            $type
        );

        $handler($command);
    }

    /**
     * @test
     */
    public function shouldThrowUserAlreadyExistsWhenCreateWithSameCpfOrCnpf() : void
    {
        $this->expectException(UserAlreadyExists::class);
        $this->expectExceptionMessage('User already exists with given CPF/CNPJ 01257534033.');

        $repository = new InMemoryUserRepository();
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $handler = new CreateUserHandler($eventBus, $eventStore, $repository);

        $cpfOrCnpj = '01257534033';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $type = 'default';
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount,
            $type
        );

        $handler($command);

        $handler($command);
    }

    /**
     * @test
     */
    public function shouldThrowUserAlreadyExistsWhenCreateWithSameEmail() : void
    {
        $this->expectException(UserAlreadyExists::class);
        $this->expectExceptionMessage('User already exists with given email foo@bar.com.');

        $repository = new InMemoryUserRepository();
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $handler = new CreateUserHandler($eventBus, $eventStore, $repository);

        $cpfOrCnpj = '01257534033';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $type = 'default';
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount,
            $type
        );
        $handler($command);

        $cpfOrCnpj = '16838637049';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $type = 'default';
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount,
            $type
        );
        $handler($command);
    }
}
