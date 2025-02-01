<?php

namespace AvocetShores\Conduit\Enums;

enum ReasoningEffort: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function fromString(string $value): ReasoningEffort
    {
        return match ($value) {
            'low' => self::LOW,
            'medium' => self::MEDIUM,
            'high' => self::HIGH,
            default => throw new \InvalidArgumentException("Invalid reasoning effort: $value"),
        };
    }
}
