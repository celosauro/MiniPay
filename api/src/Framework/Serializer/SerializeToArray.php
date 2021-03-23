<?php

declare(strict_types=1);

namespace MiniPay\Framework\Serializer;

interface SerializeToArray
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
