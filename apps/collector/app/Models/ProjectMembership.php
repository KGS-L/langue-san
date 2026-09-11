<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMembership extends Model
{
    protected $fillable = [
        'user_id',
        'approved_application_id',
        'contribution_areas',
        'is_active',
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'contribution_areas' => 'array',
            'is_active' => 'boolean',
            'started_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedApplication(): BelongsTo
    {
        return $this->belongsTo(ProjectApplication::class, 'approved_application_id');
    }
}
