<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use MiniPay\Core\User\Domain\Exception\SecretEncoderCannotCreated;

use function base64_decode;
use function base64_encode;
use function password_hash;
use function password_verify;

use const PASSWORD_ARGON2I;

class SecretEncoder
{
    private string $token;
    private string $salt;

    private function __construct(string $token, string $salt)
    {
        $this->token = $token;
        $this->salt = $salt;
    }

    public function check(string $secret): bool
    {
        return password_verify($this->generatePassword(), base64_decode($secret));
    }

    public static function fromTokenAndSalt(string $token, string $salt): self
    {
        return new self($token, $salt);
    }

    public function generate(): string
    {
        $secret = password_hash($this->generatePassword(), PASSWORD_ARGON2I);

        if (empty($secret)) {
            throw SecretEncoderCannotCreated::create();
        }

        return base64_encode($secret);
    }

    private function generatePassword(): string
    {
        return $this->token . $this->salt;
    }
}
