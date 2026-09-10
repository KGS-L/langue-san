<?php

namespace App\Enums;

enum CollectionSessionStatus: string
{
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case ABANDONED = 'abandoned';
}
