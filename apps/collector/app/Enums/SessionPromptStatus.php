<?php

namespace App\Enums;

enum SessionPromptStatus: string
{
    case PENDING = 'pending';
    case ANSWERED = 'answered';
    case SKIPPED = 'skipped';
}
