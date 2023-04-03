<?php declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Annotation as Serializer;

readonly class MiniProfileModel
{
    public function __construct(
        #[Serializer\Groups(['miniprofile'])]
        public ?string $gameState,
        #[Serializer\Groups(['miniprofile'])]
        public ?string $gameName,
        #[Serializer\Groups(['miniprofile'])]
        public ?string $richPresence = null
    ) {
    }
}
