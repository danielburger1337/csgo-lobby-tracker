<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Player;
use App\Enum\CommunityVisibilityState;
use App\Enum\PersonaState;
use Symfony\Component\Serializer\Annotation as Serializer;

readonly class PlayerSummaryModel
{
    private function __construct(
        public Player $player,
        #[Serializer\Groups(['playersummary'])]
        public string $personaName,
        #[Serializer\Groups(['playersummary'])]
        public CommunityVisibilityState $communityVisibilityState,
        #[Serializer\Groups(['playersummary'])]
        public PersonaState $personaState,
        #[Serializer\Groups(['playersummary'])]
        public string $avatarUrl,
        #[Serializer\Groups(['playersummary'])]
        public string $profileUrl,
        #[Serializer\Groups(['playersummary'])]
        public ?string $gameid = null,
        #[Serializer\Groups(['playersummary'])]
        public ?string $gameExtraInfo = null
    ) {
    }

    public static function fromResponse(Player $player, array $response): self
    {
        return new self(
            $player,
            $response['personaname'],
            CommunityVisibilityState::tryFrom($response['communityvisibilitystate']) ?? CommunityVisibilityState::Private,
            PersonaState::tryFrom($response['personastate']) ?? PersonaState::Offline,
            $response['avatarfull'],
            $response['profileurl'],
            $response['gameid'] ?? null,
            $response['gameextrainfo'] ?? null
        );
    }
}
