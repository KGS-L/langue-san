<?php

namespace App\Models;

use App\Enums\PromptType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prompt extends Model
{
    protected $fillable = [
        'code',
        'category_id',
        'french_text',
        'context',
        'type',
        'difficulty',
        'priority',
        'target_contributions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PromptType::class,
            'difficulty' => 'integer',
            'priority' => 'integer',
            'target_contributions' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
    public function sessionPrompts(): HasMany { return $this->hasMany(SessionPrompt::class); }
}
