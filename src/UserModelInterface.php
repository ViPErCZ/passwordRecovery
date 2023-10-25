<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

interface UserModelInterface
{
    public function isUserValid($email): bool;
    public function isTokenValid($token, $expirationTime): bool;
    public function saveToken($email, $token): void;
    public function resetPassword($token, $newPassword): void;
}
