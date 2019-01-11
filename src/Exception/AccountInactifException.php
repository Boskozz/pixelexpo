<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * AccountExpiredException is thrown when the user account has expired.
 *
 */
class AccountInactifException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Ce compte est inactif';
    }
}
