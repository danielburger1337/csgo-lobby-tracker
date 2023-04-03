<?php declare(strict_types=1);

namespace App\Service;

use App\Model\MiniProfileModel;
use SteamID\SteamID;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MiniProfileService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function fetchMiniProfile(string $miniProfileId, ?string $appId = null): MiniProfileModel
    {
        $response = $this->httpClient->request('GET', "https://steamcommunity.com/miniprofile/{$miniProfileId}", [
            'query' => [
                'appid' => $appId ?? 'undefined',
            ],
        ]);

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
    }

    public function fetchMiniProfileId(SteamID $steamId): string
    {
        $response = $this->httpClient->request('GET', "https://steamcommunity.com/profiles/{$steamId->getSteamID64()}");

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
}
