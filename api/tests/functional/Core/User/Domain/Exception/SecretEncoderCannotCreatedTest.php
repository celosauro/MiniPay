<?php

declare(strict_types=1);

namespace MiniPay\Tests\Core\User\Domain\Exception;

use MiniPay\Core\User\Domain\Exception\SecretEncoderCannotCreated;
use PHPUnit\Framework\TestCase;

class SecretEncoderCannotCreatedTest extends TestCase
{
    public function testShouldSecretEncoderCannotCreatedException(): void
    {
        $exception = SecretEncoderCannotCreated::create();

        $expectedMessage = 'Cannot create secret.';
        $expectedTitle = 'Cannot create secret.';

        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($expectedTitle, $exception->getTitle());
    }
}
