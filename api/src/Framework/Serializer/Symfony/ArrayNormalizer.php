<?php

declare(strict_types=1);

namespace MiniPay\Framework\Serializer\Symfony;

use ArrayObject;
use MiniPay\Framework\Serializer\DeserializeFromArray;
use MiniPay\Framework\Serializer\SerializeToArray;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function assert;
use function is_array;
use function is_subclass_of;

final class ArrayNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param mixed        $object
     * @param array<mixed> $context
     *
     * @return array<mixed>|ArrayObject<int, mixed>|bool|float|int|string|null
     */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        assert($object instanceof SerializeToArray);

        return $object->toArray();
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof SerializeToArray;
    }

    /**
     * @param mixed        $data
     * @param array<mixed> $context
     *
     * @return array<mixed>|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        return $type::fromArray($data);
    }

    /**
     * @param mixed $data
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return is_array($data) && is_subclass_of($type, DeserializeFromArray::class);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
