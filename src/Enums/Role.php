<?php

namespace JaredCannon\LaravelAI\Enums;

enum Role: string
{
    case DEVELOPER = 'developer';
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case FUNCTION = 'function';

    public static function fromString(string $role): self
    {
        return match ($role) {
            'system' => self::SYSTEM,
            'user' => self::USER,
            'assistant' => self::ASSISTANT,
            'function' => self::FUNCTION,
        };
    }
}
