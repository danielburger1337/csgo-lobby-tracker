<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Player;
use Symfony\Component\Serializer\Annotation as Serializer;

readonly class TestModel
{
    public function __construct(
        #[Serializer\Groups(['test'])]
        public Player $player,
        #[Serializer\Groups(['test'])]
        public PlayerSummaryModel $playerSummary,
        #[Serializer\Groups(['test'])]
        public MiniProfileModel $miniProfile
    ) {
    }
}
