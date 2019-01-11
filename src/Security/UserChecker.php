<?php

namespace App\Security;

use App\Entity\User as AppUser;
use App\Exception\AccountInactifException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

// user is deleted, show a generic Account Not Found message.
        if (!$user->getEstActif()) {
            throw new AccountInactifException();

        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

    }
}