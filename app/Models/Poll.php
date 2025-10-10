<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Poll extends Model
{
    protected $fillable = ['title','is_closed','is_multiple','user_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function options(): HasMany { return $this->hasMany(PollOption::class); }
    public function votes(): HasMany { return $this->hasMany(PollVote::class); }
}

