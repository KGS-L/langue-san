<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'display_order', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'display_order' => 'integer']; }
    public function prompts(): HasMany { return $this->hasMany(Prompt::class); }
    public function collectionSessions(): HasMany { return $this->hasMany(CollectionSession::class); }
}
