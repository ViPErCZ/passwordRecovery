<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

interface TokenManagerInterface
{
    public function token(int $expiration): string;

    public function isValid(string $token): bool;
}
