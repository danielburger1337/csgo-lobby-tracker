<?php declare(strict_types=1);

namespace App\Factory;

use danielburger1337\SteamOpenId\SteamOpenID;
use Nyholm\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SteamOpenIdFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory
    ) {
    }

    public function createSteamOpenId(): SteamOpenID
    {
        $callbackUri = $this->urlGenerator->generate('app_login_callback', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $realm = (new Uri($callbackUri))->withFragment('')->withQuery('')->withPath('');

        return new SteamOpenID(
            $realm->__toString(),
            $callbackUri,
            $this->httpClient,
            $this->requestFactory
        );
    }
}
