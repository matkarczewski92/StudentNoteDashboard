<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteVote extends Model
{
    protected $fillable = ['note_id','user_id','value'];

    public function note(): BelongsTo { return $this->belongsTo(Note::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

