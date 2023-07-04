<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Model\PlayerSummaryModel;
use App\Model\TestModel;
use App\Repository\PlayerRepository;
use SBSEDV\Bundle\ResponseBundle\Exception\InvalidRequestErrorException;
use SBSEDV\Bundle\ResponseBundle\Exception\ResourceNotFoundException;
use SteamID\SteamID;

class PlayerService
{
    public function __construct(
        private readonly SteamIdService $steamIdService,
        private readonly SteamWebApiService $steamWebApiService,
        private readonly MiniProfileService $miniProfileService,
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    public function findOrFail(int $id): Player
    {
        $entity = $this->playerRepository->find($id);

        if (null === $entity) {
            throw new ResourceNotFoundException('Couldnt find a player with the given id.', $id);
        }

        return $entity;
    }

    public function createPlayer(SteamID $belongsTo, SteamID $steamID, string $description = null): Player
    {
        $player = (new Player())
            ->setBelongsTo($belongsTo)
            ->setSteamId($steamID)
            ->setDescription($description)
        ;

        $playerSummary = $this->steamWebApiService->fetchPlayerSummaries([$player]);

        $steamId64 = $steamID->getSteamID64();

        if (!\array_key_exists($steamId64, $playerSummary)) {
            throw new InvalidRequestErrorException('Invalid steam id given.', 'steamid', cause: 'player.invalid_steam_id');
        }

        $miniProfileId = $this->miniProfileService->fetchMiniProfileId($player->getSteamId());

        $player->setMiniProfileId($miniProfileId);

        $this->playerRepository->save($player, true);

        return $player;
    }

    /**
     * @param Player[] $players
     *
     * @return TestModel[]
     */
    public function createSummary(array $players): array
    {
        $summaries = $this->steamWebApiService->fetchPlayerSummaries($players);

        // perform cleanup on old inactive users

        /** @var PlayerSummaryModel[] */
        $playersToFetch = [];

        foreach ($summaries as $summary) {
            if ($summary->gameid === '730') {
                $playersToFetch[] = $summary;
            }
        }

        $data = [];
        foreach ($playersToFetch as $player) {
            $miniProfile = $this->miniProfileService->fetchMiniProfile($player->player->getMiniProfileId());

            // just in case the player switched state during the api call and the miniprofile call
            if ($miniProfile->gameName !== null && $miniProfile->gameState !== null) {
                $data[] = new TestModel($player->player, $player, $miniProfile);

                $player->player->setLastSeenAt(new \DateTimeImmutable());

                $this->playerRepository->save($player->player);
            }
        }

        if (isset($player)) {
            $this->playerRepository->save($player->player, true);
        }

        \usort($data, function (TestModel $a) {
            if (\str_starts_with($a->miniProfile->richPresence ?? '', 'In Lobby')) {
                return -1;
            }

            return 1;
        });

        return $data;
    }
}
