<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Question extends Model
{
    protected $fillable = ['user_id','title','body','is_closed'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function answers(): HasMany { return $this->hasMany(Answer::class); }

    // pomocnicze
    public function canEditBy(User $user): bool {
        return $user->id === $this->user_id && $this->created_at->addMinutes(30)->isFuture();
    }
}
