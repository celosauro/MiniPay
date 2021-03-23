<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Application;

use MiniPay\Core\User\Application\SendMoney;
use MiniPay\Core\User\Application\SendMoneyHandler;
use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\Event\TransactionReceivedSubscriber;
use MiniPay\Core\User\Domain\Exception\CannotSendMoney;
use MiniPay\Core\User\Domain\Exception\TransactionUnauthorized;
use MiniPay\Core\User\Domain\Exception\UserNotFound;
use MiniPay\Core\User\Domain\StoreKeeperUser;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Core\User\Infrastructure\Persistence\DoctrineUserRepository;
use MiniPay\Framework\DomainEvent\Domain\DomainEventPublisher;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Tests\Core\User\Infrastructure\FakeTransactionAuthClient;
use MiniPay\Tests\Framework\DoctrineTestCase;
use Symfony\Component\Messenger\MessageBus;

use function assert;

class SendMoneyHandlerTest extends DoctrineTestCase
{
    /**
     * @test
     */
    public function shouldSendMoneyBetweenTwoDefaultUser(): void
    {
        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;
        $expectedPayerBalance = 50;
        $expectedPayeeBalance = 150;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createDefaultUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(true);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            $payerId->toString(),
            $payeeId->toString(),
            $valueToSend
        );

        $handler($command);

        $foundPayer = $repository->findOneByIdOrNull($payerId);
        assert($foundPayer instanceof User);
        $foundPayee = $repository->findOneByIdOrNull($payeeId);
        assert($foundPayee instanceof User);

        $this->assertEquals($expectedPayerBalance, $foundPayer->balance());
        $this->assertEquals($expectedPayeeBalance, $foundPayee->balance());
    }

    /**
     * @test
     */
    public function shouldSendMoneyFromDefaultUserToShopKeeperUser(): void
    {
        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;
        $expectedPayerBalance = 50;
        $expectedPayeeBalance = 150;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createDefaultUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(true);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            $payerId->toString(),
            $payeeId->toString(),
            $valueToSend
        );

        $handler($command);

        $foundPayer = $repository->findOneByIdOrNull($payerId);
        assert($foundPayer instanceof User);
        $foundPayee = $repository->findOneByIdOrNull($payeeId);
        assert($foundPayee instanceof User);

        $this->assertEquals($expectedPayerBalance, $foundPayer->balance());
        $this->assertEquals($expectedPayeeBalance, $foundPayee->balance());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenSendMoneyFromShopKeeperUser(): void
    {
        $this->expectException(CannotSendMoney::class);

        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createStorekeeperUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(true);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            $payerId->toString(),
            $payeeId->toString(),
            $valueToSend
        );

        $handler($command);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenPayerUserNotFound(): void
    {
        $this->expectException(UserNotFound::class);
        $this->expectExceptionMessage('User not found with given ID non-existent-payer-user.');

        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createStorekeeperUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(true);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            Id::fromString('non-existent-payer-user')->toString(),
            $payeeId->toString(),
            $valueToSend
        );

        $handler($command);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenPayeeUserNotFound(): void
    {
        $this->expectException(UserNotFound::class);
        $this->expectExceptionMessage('User not found with given ID non-existent-payee-user.');

        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createDefaultUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(true);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            $payerId->toString(),
            Id::fromString('non-existent-payee-user')->toString(),
            $valueToSend
        );

        $handler($command);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenTransactionIsUnauthorized(): void
    {
        $this->expectException(TransactionUnauthorized::class);
        $this->expectExceptionMessage(
            'Transaction Unauthorized from payerId payer-id to payeeId payee-id with value 50.'
        );

        $payerId = Id::fromString('payer-id');
        $payeeId = Id::fromString('payee-id');
        $valueToSend = 50;

        $eventBus = new MessageBus();
        $bus = new MessageBus();
        $publisher = new DomainEventPublisher([new TransactionReceivedSubscriber($eventBus)], $bus);
        $repository = new DoctrineUserRepository($this->entityManager, $publisher);

        $repository->save($this->createDefaultUser($payerId, '88498957044', 'foo@bar.com'));
        $repository->save($this->createDefaultUser($payeeId, '88498957043', 'foobar@bar.com'));

        $this->entityManager->flush();

        $transactionAuthClient = new FakeTransactionAuthClient(false);
        $handler = new SendMoneyHandler($repository, $transactionAuthClient);

        $command = new SendMoney(
            $payerId->toString(),
            $payeeId->toString(),
            $valueToSend
        );

        $handler($command);
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createDefaultUser(Id $id, string $cpfOrCnpj, string $email): DefaultUser
    {
        return DefaultUser::create(
            $id,
            'Foo Bar',
            $cpfOrCnpj,
            $email,
            new Wallet(100)
        );
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createStorekeeperUser(Id $id, string $cpfOrCnpj, string $email): StoreKeeperUser
    {
        return StoreKeeperUser::create(
            $id,
            'Foo Bar',
            $cpfOrCnpj,
            $email,
            new Wallet(100)
        );
    }
}
