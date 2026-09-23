<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Corta el login de los usuarios dados de baja.
 *
 * Symfony lo llama despues de encontrar al usuario y antes de dar por buena la
 * contrasena, asi que el mensaje sale igual aunque la clave sea correcta.
 */
class UsuarioActivoChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->estaActivo()) {
            throw new CustomUserMessageAccountStatusException(
                'Tu usuario fue dado de baja. Comunicate con un administrador.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
