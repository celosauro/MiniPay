<?php

declare(strict_types=1);

namespace MiniPay\Core\Transaction\Infrastructure\Controller;

use MiniPay\Core\User\Application\SendMoney;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class TransactionController
{
    /**
     * @Route("/transaction", methods={"POST"})
     */
    public function sendMoney(Request $request, SerializerInterface $serializer, MessageBusInterface $bus): Response
    {
        $data = (string) $request->getContent() ?? '';

        $command = $serializer->deserialize($data, SendMoney::class, 'json');
        assert($command instanceof SendMoney);

        $bus->dispatch($command);

        return new JsonResponse([], Response::HTTP_CREATED);
    }
}
