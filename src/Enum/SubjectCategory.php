<?php

namespace App\Enum;

enum SubjectCategory: string
{
    case Domestic = 'domestic';
    case European = 'european';
    case International = 'international';
    case Economy = 'economy';
    case Social = 'social';
    case Environment = 'environment';
    case Justice = 'justice';
    case Security = 'security';
    case Health = 'health';
    case Education = 'education';
    case Technology = 'technology';
    case Culture = 'culture';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Domestic => 'Domestic Politics',
            self::European => 'European Affairs',
            self::International => 'International Relations',
            self::Economy => 'Economy & Finance',
            self::Social => 'Social Issues',
            self::Environment => 'Environment & Climate',
            self::Justice => 'Justice & Law',
            self::Security => 'Security & Defense',
            self::Health => 'Health',
            self::Education => 'Education & Research',
            self::Technology => 'Technology & Digital',
            self::Culture => 'Culture & Society',
            self::Other => 'Other',
        };
    }
}
