<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Infrastructure\Persistence;

use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Core\User\Infrastructure\Persistence\InMemoryUserRepository;
use MiniPay\Framework\Id\Domain\Id;
use PHPUnit\Framework\TestCase;

class InMemoryUserRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSaveUser(): void
    {
        $repository = new InMemoryUserRepository();
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $repository->save($user);

        $foundUser = $repository->findOneByIdOrNull($id);

        $this->assertEquals($foundUser, $user);
    }

    /**
     * @test
     */
    public function shoulFindUserByCpfOrCnpj(): void
    {
        $repository = new InMemoryUserRepository();
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $repository->save($user);

        $foundUser = $repository->findOneByCpfOrCnpjOrNull($user->cpfOrCnpj());

        $this->assertEquals($foundUser, $user);
    }

     /**
      * @test
      */
    public function shoulFindUserByEmail(): void
    {
        $repository = new InMemoryUserRepository();
        $id = Id::generate();
        $user = $this->createDefaultUser($id);

        $repository->save($user);

        $foundUser = $repository->findOneByEmailOrNull($user->email());

        $this->assertEquals($foundUser, $user);
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createDefaultUser(Id $id): User
    {
        return DefaultUser::create(
            $id,
            'Foo Bar',
            '88498957044',
            'foo@bar.com',
            new Wallet(99.9)
        );
    }
}
