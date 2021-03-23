<?php

declare(strict_types=1);

namespace MiniPay\Framework\Serializer;

interface DeserializeFromArray
{
    /**
     * @param array<mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data);
}
