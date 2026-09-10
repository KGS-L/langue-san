<?php

namespace App\Models;

use App\Enums\ValidationDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Validation extends Model
{
    protected $fillable = ['contribution_id', 'validator_id', 'decision', 'san_text_corrected', 'variety_id', 'notes'];
    protected function casts(): array { return ['decision' => ValidationDecision::class]; }
    public function contribution(): BelongsTo { return $this->belongsTo(Contribution::class); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validator_id'); }
    public function variety(): BelongsTo { return $this->belongsTo(Variety::class); }
}
