<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerVote extends Model
{
    protected $fillable = ['answer_id','user_id','value'];

    public function answer(): BelongsTo { return $this->belongsTo(Answer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
