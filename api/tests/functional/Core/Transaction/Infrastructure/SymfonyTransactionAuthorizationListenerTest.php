<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\Transaction\Infrastructure;

use MiniPay\Core\User\Domain\DefaultUser;
use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\Wallet;
use MiniPay\Framework\Id\Domain\Id;
use MiniPay\Tests\Framework\DoctrineTestCase;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

class SymfonyTransactionAuthorizationListenerTest extends DoctrineTestCase
{
    /**
     * @test
     */
    public function shouldReturnValidationErrorWhenRequestTransactionWithoutSecret(): void
    {
        $userOne = $this->createDefaultUser(Id::fromString('user-1'), '88498957044', 'foo@bar.com');
        $userTwo = $this->createDefaultUser(Id::fromString('user-2'), '88498957043', 'f@bar.com');

        $response =  $this->doRequest(
            'POST',
            '/transaction',
            ['HTTP_X_USER_SECRET' => ''],
            (string) json_encode([
                'payer' => $userOne->id()->toString(),
                'payee' => $userTwo->id()->toString(),
                'value' => 1.99,
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldReturnNotFoundErrorWithInvalidSecret(): void
    {
        $userOne = $this->createDefaultUser(Id::fromString('user-1'), '88498957044', 'foo@bar.com');
        $userTwo = $this->createDefaultUser(Id::fromString('user-2'), '88498957043', 'f@bar.com');

        $response =  $this->doRequest(
            'POST',
            '/transaction',
            ['HTTP_X_USER_SECRET' => 'invalid-secret'],
            (string) json_encode([
                'payer' => $userOne->id()->toString(),
                'payee' => $userTwo->id()->toString(),
                'value' => 1.99,
            ])
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldReturnValidationErrorWhenRequestTransactionEndpointWithoutSecret(): void
    {
        $response =  $this->doRequest('POST', '/transaction');

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldDoRequestTransactionEndpointSuccessfullyWithValidSecret(): void
    {
        $userOne = $this->createDefaultUser(Id::fromString('user-1'), '88498957044', 'foo@bar.com');
        $userTwo = $this->createDefaultUser(Id::fromString('user-2'), '88498957043', 'f@bar.com');

        $response =  $this->doRequest(
            'POST',
            '/transaction',
            ['HTTP_X_USER_SECRET' => $userOne->createSecret()],
            (string) json_encode([
                'payer' => $userOne->id()->toString(),
                'payee' => $userTwo->id()->toString(),
                'value' => 1.99,
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldDoRequestToEndpointWhenSecretIsNotRequired(): void
    {
        $response =  $this->doRequest('GET', '/healthcheck');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @psalm-param Id<User> $id
     */
    private function createDefaultUser(Id $id, string $cpfOrCnpj, string $email): DefaultUser
    {
        $user = DefaultUser::create(
            $id,
            'Foo Bar',
            $cpfOrCnpj,
            $email,
            new Wallet(100)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param array<mixed> $server
     * @param array<mixed> $parameters
     * @param array<mixed> $files
     */
    private function doRequest(
        string $method,
        string $url,
        ?array $server = [],
        ?string $content = null,
        ?array $parameters = [],
        ?array $files = []
    ): Response {
         $client = self::$client;

        $client->request(
            $method,
            $url,
            $parameters ?? [],
            $files ?? [],
            $server ?? [],
            $content
        );

        return $client->getResponse();
    }
}
