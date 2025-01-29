<?php

namespace App\Security\Voter;

use App\Entity\Youth;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class YouthVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Youth
            && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Youth $youth */
        $youth = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($youth, $user),
            self::EDIT => $this->canEdit($youth, $user),
            self::DELETE => $this->canDelete($youth, $user),
            default => false,
        };
    }

    private function canView(Youth $youth, User $user): bool
    {
        // Tout utilisateur authentifié peut voir les jeunes
        return true;
    }

    private function canEdit(Youth $youth, User $user): bool
    {
        // Admin peut tout modifier
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Autres rôles peuvent modifier uniquement dans leur secteur
        return $youth->getChurch()->getSector() === $user->getSector();
    }

    private function canDelete(Youth $youth, User $user): bool
    {
        // Seuls Admin et Dirigeant peuvent supprimer
        if (!in_array('ROLE_DIRIGEANT', $user->getRoles())) {
            return false;
        }

        // Admin peut tout supprimer
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Dirigeant peut supprimer dans son secteur
        return $youth->getChurch()->getSector() === $user->getSector();
    }
} 