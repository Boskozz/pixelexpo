<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdate
{
    private $oldPassword;

    /**
     * @Assert\Length(min=8, minMessage="Votre mot de passe doit faire au moins 8 caractères", max=16, maxMessage="Votre mot de passe doit être inférieur à 16 caractères")
     */
    private $newPassword;

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}
