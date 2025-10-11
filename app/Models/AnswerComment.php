<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class AnswerComment extends Model
{
    protected $fillable = ['answer_id','user_id','body','parent_id'];

    public function answer(): BelongsTo { return $this->belongsTo(Answer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(self::class, 'parent_id')->oldest(); }
}

