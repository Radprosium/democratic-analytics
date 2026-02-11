<?php

namespace App\Enum;

enum SubjectStatus: string
{
    case Active = 'active';
    case Resolved = 'resolved';
    case Dormant = 'dormant';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Resolved => 'Resolved',
            self::Dormant => 'Dormant',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Active => 'badge-active',
            self::Resolved => 'badge-resolved',
            self::Dormant => 'badge-dormant',
        };
    }
}
