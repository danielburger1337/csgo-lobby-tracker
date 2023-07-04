<?php declare(strict_types=1);

namespace App\Service;

use App\Model\MiniProfileModel;
use SteamID\SteamID;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MiniProfileService
{
    /**
     * @param string[] $webProxies
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'csv:HTTP_WEB_PROXIES')]
        private readonly array $webProxies
    ) {
    }

    public function fetchMiniProfile(string $miniProfileId, string $appId = null): MiniProfileModel
    {
        return $this->cache->get('miniprofile_'.$miniProfileId, function (ItemInterface $item) use ($miniProfileId, $appId) {
            $item->expiresAfter(new \DateInterval('PT15S'));

            $response = $this->sendRequest('GET', "https://steamcommunity.com/miniprofile/{$miniProfileId}?appid=".$appId ?? 'undefined');

            $content = $response->getContent();

            $crawler = new Crawler($content);

            try {
                $gameState = $crawler->filter('.miniprofile_game_details .game_state')->text();

                if ($gameState === '') {
                    $gameState = null;
                }
            } catch (\InvalidArgumentException) {
                $gameState = null;
            }

            try {
                $richPresence = $crawler->filter('.miniprofile_game_details .rich_presence')->text();

                if ($richPresence === '') {
                    $richPresence = null;
                }
            } catch (\InvalidArgumentException) {
                $richPresence = null;
            }

            try {
                $gameName = $crawler->filter('.miniprofile_game_details .miniprofile_game_name')->text();

                if ($gameName === '') {
                    $gameName = null;
                }
            } catch (\InvalidArgumentException) {
                $gameName = null;
            }

            return new MiniProfileModel($gameState, $gameName, $richPresence);
        });
    }

    public function fetchMiniProfileId(SteamID $steamId): string
    {
        $response = $this->sendRequest('GET', "https://steamcommunity.com/profiles/{$steamId->getSteamID64()}");

        $content = $response->getContent();

        $crawler = new Crawler($content);

        try {
            $miniProfileId = $crawler->filter('.playerAvatar.profile_header_size')->first()->attr('data-miniprofile');
        } catch (\InvalidArgumentException) {
            $miniProfileId = null;
        }

        if (null === $miniProfileId) {
            throw new \RuntimeException('Failed to parse a miniprofile id from steamcommunity profile.');
        }

        return $miniProfileId;
    }

    private function sendRequest(string $method, string $url): ResponseInterface
    {
        $proxyCount = \count($this->webProxies);

        if ($proxyCount === 0 || \random_int(1, $proxyCount + 1) === 1) {
            return $this->httpClient->request($method, $url);
        }

        $proxy = $this->webProxies[\array_rand($this->webProxies)];

        $proxyUrl = \str_replace('{{url}}', \urlencode($url), $proxy);
        $proxyUrl = \str_replace('{{method}}', \urlencode($method), $proxyUrl);

        $response = $this->httpClient->request($method, $proxyUrl)->toArray();

        $response = new MockResponse($response['content'], [
            'http_code' => $response['status'],
            'response_headers' => $response['headers'],
        ]);

        return MockResponse::fromRequest($method, $proxyUrl, [], $response);
    }
}
