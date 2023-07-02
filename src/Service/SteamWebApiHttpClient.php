<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SteamWebApiHttpClient implements HttpClientInterface
{
    use DecoratorTrait;

    /**
     * @param string[] $steamWebApiKeys
     */
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'csv:STEAM_WEB_API_KEY')]
        private readonly array $steamWebApiKeys
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // Pick a random WebAPI key
        @$options['query']['key'] = $this->getRandomWebApiKey();
        $options['query']['format'] = 'json';

        $options['base_uri'] = 'https://api.steampowered.com';

        return $this->client->request($method, $url, $options);
    }

    public function getRandomWebApiKey(): string
    {
        return $this->steamWebApiKeys[\array_rand($this->steamWebApiKeys)];
    }
}
