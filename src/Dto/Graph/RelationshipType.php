<?php

namespace App\Dto\Graph;

enum RelationshipType: string
{
    case MemberOf = 'MEMBER_OF';
    case AllyOf = 'ALLY_OF';
    case FamilyOf = 'FAMILY_OF';
    case HoldsPosition = 'HOLDS_POSITION';
    case MentionedIn = 'MENTIONED_IN';

    public function cypherLabel(): string
    {
        return $this->value;
    }
}