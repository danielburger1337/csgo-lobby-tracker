<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Model\PlayerSummaryModel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SteamWebApiService
{
    public function __construct(
        private readonly SteamWebApiHttpClient $steamWebApiHttpClient,
        private readonly CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl.player_summaries')]
        private readonly string $cacheTtl
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
        $steamIds = \array_map(fn (Player $p) => (string) $p->getSteamId()->getSteamID64(), $players);
        $steamIds = \implode(',', $steamIds);

        return $this->cache->get('player_summaries_'.\sha1($steamIds), function (ItemInterface $item) use ($steamIds, $players) {
            $item->expiresAfter(new \DateInterval($this->cacheTtl));

            $response = $this->steamWebApiHttpClient->request('GET', '/ISteamUser/GetPlayerSummaries/v0002', [
                'query' => [
                    'steamids' => $steamIds,
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
        });
    }
}
