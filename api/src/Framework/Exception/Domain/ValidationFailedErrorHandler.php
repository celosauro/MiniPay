<?php

declare(strict_types=1);

namespace MiniPay\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Throwable;

use function get_class;
use function sprintf;
use function str_replace;

class ValidationFailedErrorHandler implements ErrorHandler
{
    private NewRelicInteractorInterface $newRelicLogger;

    public function __construct(NewRelicInteractorInterface $newRelicLogger)
    {
        $this->newRelicLogger = $newRelicLogger;
    }

    public function canHandleWith(Throwable $exception): bool
    {
        return $exception instanceof ValidationFailedException;
    }

    public function handle(Throwable $exception): JsonResponse
    {
        if (! $this->canHandleWith($exception)) {
            throw new InvalidArgumentException(sprintf('Error %s cannot be handled.', get_class($exception)));
        }

        $violations = $exception instanceof ValidationFailedException
            ? $exception->getViolations()
            : [];

        $responseData = ['detail' => 'Validation Failed'];
        foreach ($violations as $violation) {
            $violationData = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];

            $violationData += $this->formatViolationsParameters($violation->getParameters());

            $responseData['violations'][] = $violationData;
        }

        $this->newRelicLogger->noticeThrowable($exception);

        return new JsonResponse($responseData, StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    /**
     * @param string[] $parmeters
     *
     * @return array<string, array<string, string>>
     */
    private function formatViolationsParameters(array $parmeters): array
    {
        $formattedParameters = [];
        foreach ($parmeters as $parameterKey => $parameterValue) {
            $parameterKeyWithoutBraces = $this->removeViolationParameterBraces($parameterKey);
            $formattedParameters['parameters'][$parameterKeyWithoutBraces] = $parameterValue;
        }

        return $formattedParameters;
    }

    private function removeViolationParameterBraces(string $parameterKey): string
    {
        return str_replace(['{{ ', ' }}'], '', $parameterKey);
    }
}
