<?php

declare(strict_types=1);

namespace MiniPay\Core\Transaction\Infrastructure;

use MiniPay\Core\User\Domain\User;
use MiniPay\Core\User\Domain\UserRepository;
use MiniPay\Framework\Id\Domain\Id;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function json_decode;
use function preg_match;

class SymfonyTransactionAuthorizationListener
{
    private Request $request;
    private string $userId;
    private ?string $userSecret;

    private UserRepository $userRepository;

    /**
     * Pattern to paths
     *
     * @var string[]
     */
    private array $requiredSecretPath = ['/transaction$/'];

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->setProperties($event->getRequest());

        if (! $this->isRequiredSecretPath()) {
            return;
        }

        if (! $this->userSecret) {
            $event->setResponse($this->responseForHasNotSecret());

            return;
        }

        $foundUser = $this->foundUser();

        if (! $foundUser instanceof User || ! $foundUser->checkSecret($this->userSecret)) {
            $event->setResponse($this->responseForUserNotFound());

            return;
        }
    }

    private function setProperties(Request $request): void
    {
        $this->request = $request;

        $data = (string) $request->getContent();
        $data = json_decode($data, true);
        $payerId = $data['payer'] ?? '';

        $this->userId = $payerId;
        $this->userSecret = $this->request->headers->get('X-User-Secret');
    }

    private function isRequiredSecretPath(): bool
    {
        foreach ($this->requiredSecretPath as $path) {
            preg_match($path, $this->request->getPathInfo(), $matches);

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    private function responseForUserNotFound(): JsonResponse
    {
        $message = 'No user was found with the given id';

        return $this->prepareResponse($message, JsonResponse::HTTP_NOT_FOUND);
    }

    private function responseForHasNotSecret(): JsonResponse
    {
        $message = 'X-User-Secret header is required for this request.';

        return $this->prepareResponse($message, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function prepareResponse(string $detail, int $httpCode): JsonResponse
    {
        return new JsonResponse(['detail' => $detail], $httpCode);
    }

    private function foundUser(): ?User
    {
        return $this->userRepository->findOneByIdOrNull(Id::fromString($this->userId));
    }
}
