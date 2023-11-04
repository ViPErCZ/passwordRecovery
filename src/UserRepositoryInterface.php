<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

interface UserRepositoryInterface
{
    public function hasUser(string $email): bool;
    public function saveToken(string $email, string $token): void;
    public function resetPassword(string $token, string $newPassword): void;
}
