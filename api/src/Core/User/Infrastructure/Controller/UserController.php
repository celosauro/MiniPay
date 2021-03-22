<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Infrastructure\Controller;

use MiniPay\Core\User\Application\CreateUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class UserController
{
    /**
     * @Route("/users", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, MessageBusInterface $bus): Response
    {
        $data = (string) $request->getContent() ?? '';

        $command = $serializer->deserialize($data, CreateUser::class, 'json');
        assert($command instanceof CreateUser);

        $bus->dispatch($command);

        return new JsonResponse([], Response::HTTP_CREATED);
    }
}
