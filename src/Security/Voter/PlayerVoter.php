<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Player;
use App\Security\SteamUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlayerVoter extends Voter
{
    public const ATTRIBUTE_DELETE = 'player.delete';

    private const ATTRIBUTES = [
        self::ATTRIBUTE_DELETE,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Player && \in_array($attribute, self::ATTRIBUTES, true);
    }

    /**
     * @param Player $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof SteamUser) {
            return false;
        }

        switch ($attribute) {
            case self::ATTRIBUTE_DELETE:
                return $subject->getBelongsTo()->getSteamID64() === $user->getId()->getSteamID64();
        }

        return false;
    }
}
