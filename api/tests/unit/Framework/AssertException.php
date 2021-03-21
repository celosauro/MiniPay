<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework;

use Lcobucci\ErrorHandling\Problem\Detailed;
use Lcobucci\ErrorHandling\Problem\Titled;
use Throwable;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait AssertException
{
    /**
     * @param class-string              $expectClass
     * @param array<string, mixed>|null $details
     */
    public function assertException(string $expectClass, callable $callback, ?string $title, ?array $details): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            self::assertInstanceOf($expectClass, $exception, 'An invalid exception was thrown');

            if ($exception instanceof Titled) {
                self::assertEquals($title, $exception->getTitle());
            }

            if ($exception instanceof Detailed) {
                self::assertEquals($details, $exception->getExtraDetails());
            }

            return;
        }

        $this->fail('No exception was thrown');
    }
}
