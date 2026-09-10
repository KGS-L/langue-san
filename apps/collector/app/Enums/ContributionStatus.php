<?php

namespace App\Enums;

enum ContributionStatus: string
{
    case PENDING = 'pending';
    case TRANSCRIBED = 'transcribed';
    case VALIDATED_ONCE = 'validated_once';
    case VALIDATED_TWICE = 'validated_twice';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
