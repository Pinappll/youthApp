<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Event
            && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($event, $user),
            self::EDIT => $this->canEdit($event, $user),
            self::DELETE => $this->canDelete($event, $user),
            default => false,
        };
    }

    private function canView(Event $event, User $user): bool
    {
        // Tout utilisateur authentifié peut voir les événements
        return true;
    }

    private function canEdit(Event $event, User $user): bool
    {
        // Admin peut tout modifier
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Dirigeant peut modifier les événements de son secteur
        if (in_array('ROLE_DIRIGEANT', $user->getRoles())) {
            return $event->getSector() === $user->getSector();
        }

        // Enseignant peut modifier les événements de son secteur pendant 3 jours
        if (in_array('ROLE_ENSEIGNANT', $user->getRoles())) {
            $createdAt = $event->getCreatedAt();
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            return $event->getSector() === $user->getSector() 
                && $diff->days <= 3;
        }

        return false;
    }

    private function canDelete(Event $event, User $user): bool
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
        return $event->getSector() === $user->getSector();
    }
} 