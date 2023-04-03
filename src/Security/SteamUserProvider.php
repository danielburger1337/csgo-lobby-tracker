<?php declare(strict_types=1);

namespace App\Security;

use danielburger1337\SteamOpenId\SteamOpenID;
use SteamID\SteamID;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SteamUserProvider implements UserProviderInterface
{
    public function __construct(
        #[Autowire('%steam_web_api_key%')]
        private string $steamWebApiKey,
        private SteamOpenID $steamOpenID
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $summary = null;

        try {
            $steamId = new SteamID($identifier);

            if (!$steamId->isValid() || $steamId->type !== SteamID::TYPE_INDIVIDUAL) {
                $steamId = null;
            }
        } catch (\Throwable) {
            $steamId = null;
        }

        if (null !== $steamId) {
            $summary = $this->steamOpenID->fetchUserInfo($steamId->getSteamID64(), $this->steamWebApiKey);
        }

        if (null === $summary) {
            throw (new UserNotFoundException())
                ->setUserIdentifier($identifier)
            ;
        }

        return new SteamUser($steamId, $summary['personaname'], $summary['avatarfull']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass(string $class): bool
    {
        return \is_a($class, SteamUser::class, true);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }
}
