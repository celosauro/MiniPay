<?php

declare(strict_types=1);

namespace MiniPay\Example\Infrastructure\Controller;

use MiniPay\Example\Application\SayHello;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class HelloController
{
    /**
     * @Route("/hello/{name}", methods={"POST"}, name="exemplo_hello")
     */
    public function hello(Request $request, SerializerInterface $serializer, MessageBusInterface $bus): Response
    {
        $name = $request->attributes->get('name');
        $body = (string) $request->getContent();

        $command = $serializer->deserialize($body, SayHello::class, 'json');
        assert($command instanceof SayHello);
        $command = $command->withName($name);

        $bus->dispatch($command);

        return new JsonResponse(['msg' => 'sucesso'], Response::HTTP_CREATED);
    }
}
