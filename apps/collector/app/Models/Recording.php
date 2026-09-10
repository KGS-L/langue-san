<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recording extends Model
{
    protected $fillable = ['contribution_id', 'disk', 'path', 'mime_type', 'size_bytes', 'duration_ms', 'quality_status'];
    public function contribution(): BelongsTo { return $this->belongsTo(Contribution::class); }
}
