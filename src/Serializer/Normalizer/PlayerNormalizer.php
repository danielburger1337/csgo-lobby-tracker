<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Player;
use SBSEDV\Bundle\ResponseBundle\Model\Link;
use SBSEDV\Bundle\ResponseBundle\Model\LinkCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlayerNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer
    ) {
    }

    /**
     * @param Player $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var array */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['_links'] = new LinkCollection([
            'self' => new Link('api_v1_player_delete', ['playerId' => $object->getId()]),
            'steam_profile' => new Link('https://steamcommunity.com/profiles/'.$object->getSteamId()->getSteamID64()),
            'steam_miniprofile' => new Link('https://steamcommunity.com/miniprofile/'.$object->getMiniProfileId()),
            'csgostats' => new Link('https://csgostats.gg/player/'.$object->getSteamId()->getSteamID64()),
        ]);

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Player::class => true,
        ];
    }
}
