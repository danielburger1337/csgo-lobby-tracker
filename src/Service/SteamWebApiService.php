<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Model\PlayerSummaryModel;

class SteamWebApiService
{
    public function __construct(
        private readonly SteamWebApiHttpClient $steamWebApiHttpClient
    ) {
    }

    /**
     * @param Player[] $players
     *
     * @return array<string, PlayerSummaryModel>
     */
    public function fetchPlayerSummaries(array $players): array
    {
        $list = [];

        // API only allows 100 steamids per request
        foreach (\array_chunk($players, 100) as $chunk) {
            $x = $this->doFetchPlayerSummaries($chunk);

            $list = $list + $x;
        }

        return $list;
    }

    /**
     * @param Player[] $players
     *
     * @return PlayerSummaryModel[]
     */
    private function doFetchPlayerSummaries(array $players): array
    {
        $profileIds = \array_map(fn (Player $p) => (string) $p->getSteamId()->getSteamID64(), $players);

        $response = $this->steamWebApiHttpClient->request('GET', '/ISteamUser/GetPlayerSummaries/v0002', [
            'query' => [
                'steamids' => \implode(',', $profileIds),
            ],
        ])->toArray();

        $list = [];

        foreach ($players as $player) {
            foreach ($response['response']['players'] as $p) {
                if ($p['steamid'] === $player->getSteamId()->getSteamID64()) {
                    $list[$p['steamid']] = PlayerSummaryModel::fromResponse($player, $p);

                    continue 2;
                }
            }

            // $list[$p['steamid']] = null;
        }

        return $list;
    }
}
