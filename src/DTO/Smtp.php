<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery\DTO;

final class Smtp
{
    public function __construct(
        public readonly string $host,
        public readonly string $email,
        public readonly string $password,
        public readonly string|null $encryption = null
    ) {
    }
}
