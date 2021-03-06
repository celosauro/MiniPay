<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Application;

use MiniPay\Core\User\Application\CreateUser;
use MiniPay\Core\User\Application\CreateUserHandler;
use MiniPay\Core\User\Domain\Exception\CannotCreateUser;
use MiniPay\Core\User\Domain\Exception\UserAlreadyExists;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Infrastructure\Persistence\InMemoryUserRepository;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_string;

class CreateUserHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnUser(): void
    {
        $repository = new InMemoryUserRepository();
        $handler = new CreateUserHandler($repository);

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

        $userId = $command->id;
        assert(is_string($userId));

        $secret = $handler($command);

        $createdUser = $repository->findOneByIdOrNull(Id::fromString($command->id ?? ''));
        assert($createdUser instanceof User);

        $this->assertNotNull($createdUser);
        $this->assertEquals($command->cpfOrCnpj, $createdUser->cpfOrCnpj());
        $this->assertEquals($command->fullName, $createdUser->fullName());
        $this->assertEquals($command->email, $createdUser->email());
        $this->assertEquals($command->walletAmount, $createdUser->balance());
        $this->assertTrue($createdUser->checkSecret($secret));
    }

    /**
     * @test
     */
    public function shouldThrowCannotCreateUserWithInvalidType(): void
    {
        $this->expectException(CannotCreateUser::class);

        $repository = new InMemoryUserRepository();
        $handler = new CreateUserHandler($repository);

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
    public function shouldThrowUserAlreadyExistsWhenCreateWithSameCpfOrCnpf(): void
    {
        $this->expectException(UserAlreadyExists::class);
        $this->expectExceptionMessage('User already exists with given CPF/CNPJ 01257534033.');

        $repository = new InMemoryUserRepository();
        $handler = new CreateUserHandler($repository);

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
    public function shouldThrowUserAlreadyExistsWhenCreateWithSameEmail(): void
    {
        $this->expectException(UserAlreadyExists::class);
        $this->expectExceptionMessage('User already exists with given email foo@bar.com.');

        $repository = new InMemoryUserRepository();
        $handler = new CreateUserHandler($repository);

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
