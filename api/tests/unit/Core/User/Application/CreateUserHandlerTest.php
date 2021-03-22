<?php

namespace MiniPay\Tests\Core\User\Application;

use MiniPay\Core\User\Application\CreateUser;
use MiniPay\Core\User\Application\CreateUserHandler;
use MiniPay\Core\User\Infrastructure\Persistence\InMemoryUserRepository;
use MiniPay\Framework\DomainEvent\Infrastructure\InMemoryEventStore;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CreateUserHandlerTest extends TestCase
{
//    protected function setUp() : void
//    {
//        parent::setUp();
//
//        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
//        $subscriber = new PersistDomainEventSubscriber($eventStore);
//        $eventBus = new MessageBus();
//        $publisher = new DomainEventPublisher([$subscriber], $eventBus);
//
//        $this->repository = new DoctrineEnvelopeRepository($this->entityManager, $publisher);
//    }
//
    /**
     * @test
     */
    public function shouldCreateADefaultUser() : void
    {
        $repository = new InMemoryUserRepository();
        $eventBus = new MessageBus();
        $eventStore = new InMemoryEventStore(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $handler = new CreateUserHandler($eventBus, $eventStore, $repository);

        $cpfOrCnpj = '01257534033';
        $fullName = 'Foo Bar';
        $email = 'foo@bar.com';
        $walletAmount = 99.99;
        $command = new CreateUser(
            $cpfOrCnpj,
            $fullName,
            $email,
            $walletAmount
        );

        $handler($command);

        $createdUser = $repository->findOneById(Id::fromString($command->id));

        $this->assertNotNull($createdUser);
        $this->assertEquals($command->cpfOrCnpj, $createdUser->cpfOrCnpj());
        $this->assertEquals($command->fullName, $createdUser->fullName());
        $this->assertEquals($command->email, $createdUser->email());
        $this->assertEquals($command->walletAmount, $createdUser->balance());
    }
}
