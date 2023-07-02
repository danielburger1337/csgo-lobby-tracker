<?php declare(strict_types=1);

namespace App\Security;

use App\Service\SteamWebApiHttpClient;
use danielburger1337\SteamOpenId\SteamOpenID;
use SteamID\SteamID;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SteamUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly SteamOpenID $steamOpenID,
        private readonly SteamWebApiHttpClient $steamWebApiHttpClient
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $summary = null;

        try {
            $steamId = new SteamID($identifier);

            if (!$steamId->isValid() || $steamId->type !== SteamID::TYPE_INDIVIDUAL) {
                throw new \InvalidArgumentException('SteamID is not a valid "TYPE_INDIVIDUAL" id.');
            }
        } catch (\Exception $e) {
            throw (new UserNotFoundException(previous: $e))
                ->setUserIdentifier($identifier)
            ;
        }

        $steamId64 = $steamId->getSteamID64();

        $summary = $this->steamOpenID->fetchUserInfo($steamId64, $this->steamWebApiHttpClient->getRandomWebApiKey());

        if (null === $summary) {
            throw (new UserNotFoundException())->setUserIdentifier($identifier);
        }

        return new SteamUser($steamId, $summary['personaname'], $summary['avatarfull']);
    }

    public function supportsClass(string $class): bool
    {
        return \is_a($class, SteamUser::class, true);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }
}
