<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionSegment extends Model
{
    protected $fillable = [
        'contribution_id',
        'position',
        'start_ms',
        'end_ms',
        'san_text',
        'french_translation',
        'variety_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'start_ms' => 'integer',
            'end_ms' => 'integer',
        ];
    }

    public function contribution(): BelongsTo { return $this->belongsTo(Contribution::class); }
    public function variety(): BelongsTo { return $this->belongsTo(Variety::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
}
