<?php

namespace App\Models;

use App\Enums\SessionPromptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionPrompt extends Model
{
    protected $fillable = ['collection_session_id', 'prompt_id', 'position', 'status'];
    protected function casts(): array { return ['status' => SessionPromptStatus::class, 'position' => 'integer']; }
    public function session(): BelongsTo { return $this->belongsTo(CollectionSession::class, 'collection_session_id'); }
    public function prompt(): BelongsTo { return $this->belongsTo(Prompt::class); }
}
