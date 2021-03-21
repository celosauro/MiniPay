<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework\Exception\Domain;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractor;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use MiniPay\Framework\Exception\Domain\ErrorHandler;
use MiniPay\Framework\Exception\Domain\ValidationFailedErrorHandler;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

use function assert;
use function json_encode;

class ValidationFailedErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;

    protected function setUp(): void
    {
        $newRelicLogger = $this->getMockBuilder(NewRelicInteractor::class)->getMock();
        assert($newRelicLogger instanceof NewRelicInteractor);

        $this->handler = new ValidationFailedErrorHandler($newRelicLogger);
    }

    /** @dataProvider providerSupportedExceptions */
    public function testShouldIndicateCanHandleException(Throwable $exception): void
    {
        $canDeal = $this->handler->canHandleWith($exception);

        $this->assertEquals(true, $canDeal);
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldIndicateCannotHandleException(Throwable $exception): void
    {
        $canDeal = $this->handler->canHandleWith($exception);

        $this->assertEquals(false, $canDeal);
    }

    /**
     * @param string[] $expectedResponse
     *
     * @dataProvider providerSupportedExceptions
     */
    public function testShouldHandle(Throwable $exception, array $expectedResponse, int $expectedStatusCode): void
    {
        $response = $this->handler->handle($exception);

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedResponse) ?: '',
            $response->getContent() ?: ''
        );
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    /** @dataProvider providerNotSupportedExceptions */
    public function testShouldNotHandle(Throwable $error): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->handler->handle($error);
    }

    /** @return array<mixed> */
    public function providerSupportedExceptions(): array
    {
        $exceptions = [
            0 => new ValidationFailedException(new stdClass(), new ConstraintViolationList([])),
            1 => $this->createValidationFailedException(
                [
                    [
                        'message' => 'The name must be at least 3 characters long',
                        'template' => 'The name must be at least {{ limit }} characters long',
                        'parameters' => [
                            '{{ limit }}' => 3,
                            '{{ value }}' => 'va',
                        ],
                        'property' => 'name',
                        'value' => 'va',
                    ],
                ],
            ),
            2 => $this->createValidationFailedException(
                [
                    [
                        'message' => 'The name must be at least 3 characters long',
                        'template' => 'The name must be at least {{ limit }} characters long',
                        'parameters' => [
                            'limit' => 3,
                            'value' => 'va',
                        ],
                        'property' => 'name',
                        'value' => 'va',
                    ],
                    [
                        'message' => 'This value is not a valid email address.',
                        'template' => 'This value is not a valid email address.',
                        'parameters' => ['value' => 'fulano.example.com'],
                        'property' => 'email',
                        'value' => 'fulano.example.com',
                    ],
                ],
            ),
        ];

        $expectedResponses = [
            0 => ['detail' => 'Validation Failed'],
            1 => [
                'detail' => 'Validation Failed',
                'violations' => [
                    [
                        'field' => 'name',
                        'message' => 'The name must be at least 3 characters long',
                        'parameters' => [
                            'limit' => 3,
                            'value' => 'va',
                        ],
                    ],
                ],
            ],
            2 => [
                'detail' => 'Validation Failed',
                'violations' => [
                    [
                        'field' => 'name',
                        'message' => 'The name must be at least 3 characters long',
                        'parameters' => [
                            'limit' => 3,
                            'value' => 'va',
                        ],
                    ],
                    [
                        'field' => 'email',
                        'message' => 'This value is not a valid email address.',
                        'parameters' => ['value' => 'fulano.example.com'],
                    ],
                ],
            ],
        ];

        return [
            'when there is just a detail information ' => [
                $exceptions[0],
                $expectedResponses[0],
                400,
            ],
            'when there is a full validation information' => [
                $exceptions[1],
                $expectedResponses[1],
                400,
            ],
            'where there are many violations' => [
                $exceptions[2],
                $expectedResponses[2],
                400,
            ],
        ];
    }

    /** @return array<mixed> */
    public function providerNotSupportedExceptions(): array
    {
        return [
            [new Exception()],
            [new RuntimeException()],
            [new InvalidArgumentException()],
        ];
    }

    /** @param array<mixed> $violations */
    protected function createValidationFailedException(array $violations): ValidationFailedException
    {
        $list = [];
        foreach ($violations as $violation) {
            $list[] = new ConstraintViolation(
                $violation['message'],
                $violation['template'],
                $violation['parameters'],
                '',
                $violation['property'],
                $violation['value']
            );
        }

        $constraints = new ConstraintViolationList($list);

        return new ValidationFailedException(new stdClass(), $constraints);
    }
}
