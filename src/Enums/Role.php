<?php

namespace AvocetShores\Conduit\Enums;

use Illuminate\Support\Str;

enum Role: string
{
    /**
     * Newer role for instructing reasoning models like o1
     */
    case DEVELOPER = 'developer';

    /**
     * The system role is used by most models to give initial instructions to the model.
     */
    case SYSTEM = 'system';

    /**
     * User messages are the main input to the model.
     */
    case USER = 'user';

    case ASSISTANT = 'assistant';

    public static function fromString(string $role): self
    {
        return match (Str::lower($role)) {
            'system' => self::SYSTEM,
            'assistant' => self::ASSISTANT,
            'developer' => self::DEVELOPER,
            default => self::USER,
        };
    }
}
