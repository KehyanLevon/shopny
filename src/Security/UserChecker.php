<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified() && in_array('ROLE_USER', $user->getRoles(), true)) {
            throw new CustomUserMessageAuthenticationException('EMAIL_NOT_VERIFIED');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    { }
}
