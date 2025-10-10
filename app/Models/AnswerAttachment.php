<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerAttachment extends Model
{
    protected $fillable = ['answer_id','path','original_name','size'];

    public function answer(): BelongsTo { return $this->belongsTo(Answer::class); }

    public function url(): string { return asset('storage/' . $this->path); }
}
