<?php

namespace App\Security\Voter;

use App\Entity\Youth;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class YouthVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Youth;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // For now, allow all actions for authenticated users
        // You can modify this logic based on your requirements
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Youth $youth */
        $youth = $subject;

        return match($attribute) {
            self::VIEW => true, // Everyone can view
            self::EDIT => true, // Everyone can edit
            self::DELETE => true, // Everyone can delete
            default => false,
        };
    }
}