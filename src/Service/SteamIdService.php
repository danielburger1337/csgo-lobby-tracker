<?php declare(strict_types=1);

namespace App\Service;

use SteamID\SteamID;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamIdService
{
    private const PROFILES_URL_PREFIX = 'https://steamcommunity.com/profiles/';
    private const VANITY_URL_PREFIX = 'https://steamcommunity.com/id/';

    public function __construct(
        private HttpClientInterface $steamApiClient
    ) {
    }

    /**
     * Resolve a SteamId from a vanity url, a profile url or any steamId format.
     *
     * @return SteamID|null The steam Id or null if the given id is invalid.
     */
    public function resolveSteamId(string $id): ?SteamID
    {
        if (\str_starts_with($id, self::PROFILES_URL_PREFIX)) {
            $id = \substr($id, \strlen(self::PROFILES_URL_PREFIX));
        } elseif (\str_starts_with($id, self::VANITY_URL_PREFIX)) {
            $id = \substr($id, \strlen(self::VANITY_URL_PREFIX));
        }

        if (\str_ends_with($id, '/')) {
            $id = \substr($id, 0, -1);
        }

        try {
            $steamId = new SteamID($id);

            if ($steamId->isValid()) {
                if ($steamId->type === SteamID::TYPE_INDIVIDUAL) {
                    return $steamId;
                }

                // If the ID is valid but doesnt belong to a user (maybe it belongs to a group)
                return null;
            }
        } catch (\Throwable) {
        }

        $response = $this->steamApiClient->request('GET', '/ISteamUser/ResolveVanityURL/v0001', [
            'query' => [
                'vanityurl' => $id,
            ],
        ]);

        $data = $response->toArray()['response'];

        if ($data['success'] === 1) {
            return new SteamID($data['steamid']);
        }

        return null;
    }
}
