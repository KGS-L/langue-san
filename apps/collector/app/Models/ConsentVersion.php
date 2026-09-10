<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentVersion extends Model
{
    protected $fillable = ['version', 'content', 'allow_training', 'allow_audio_publication', 'is_active', 'published_at'];
    protected function casts(): array { return ['allow_training' => 'boolean', 'allow_audio_publication' => 'boolean', 'is_active' => 'boolean', 'published_at' => 'datetime']; }
    public function consents(): HasMany { return $this->hasMany(UserConsent::class); }
}
