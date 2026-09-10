<?php

namespace App\Models;

use App\Enums\CollectionSessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSession extends Model
{
    protected $fillable = ['contributor_profile_id', 'category_id', 'status', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'status' => CollectionSessionStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function contributorProfile(): BelongsTo { return $this->belongsTo(ContributorProfile::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function sessionPrompts(): HasMany { return $this->hasMany(SessionPrompt::class); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
}
