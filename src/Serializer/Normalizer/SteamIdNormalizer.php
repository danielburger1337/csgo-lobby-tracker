<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use SteamID\SteamID;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SteamIdNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param SteamID $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        return $object->getSteamID64();
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof SteamID;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
