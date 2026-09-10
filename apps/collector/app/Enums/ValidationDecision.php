<?php

namespace App\Enums;

enum ValidationDecision: string
{
    case APPROVE = 'approve';
    case CORRECT = 'correct';
    case REJECT = 'reject';
}
