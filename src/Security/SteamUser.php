<?php declare(strict_types=1);

namespace App\Security;

use SteamID\SteamID;
use Symfony\Component\Security\Core\User\UserInterface;

class SteamUser implements UserInterface
{
    public function __construct(
        private readonly SteamID $steamID,
        private readonly string $personaName,
        private readonly string $avatarUrl,
    ) {
    }

    public function getId(): SteamID
    {
        return $this->steamID;
    }

    public function getPersonaName(): string
    {
        return $this->personaName;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    public function getUserIdentifier(): string
    {
        return $this->steamID->getSteamID64();
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }
}
