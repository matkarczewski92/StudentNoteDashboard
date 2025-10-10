<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class PollOption extends Model
{
    protected $fillable = ['poll_id','label'];

    public function poll(): BelongsTo { return $this->belongsTo(Poll::class); }
    public function votes(): HasMany { return $this->hasMany(PollVote::class); }
}

