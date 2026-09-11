<?php

namespace App\Models;

use App\Enums\ProjectApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApplication extends Model
{
    protected $fillable = [
        'user_id',
        'contribution_areas',
        'experience',
        'motivation',
        'availability',
        'portfolio_url',
        'san_connection',
        'status',
        'reviewed_by',
        'reviewed_at',
        'decision_reason',
    ];

    protected function casts(): array
    {
        return [
            'contribution_areas' => 'array',
            'status' => ProjectApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
