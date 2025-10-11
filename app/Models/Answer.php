<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Answer extends Model
{
    protected $fillable = ['question_id','user_id','body'];

    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function votes(): HasMany { return $this->hasMany(AnswerVote::class); }

    public function thumbsUpCount(): int  { return $this->votes()->where('value', 1)->count(); }
    public function thumbsDownCount(): int{ return $this->votes()->where('value', -1)->count(); }

    public function canEditBy(User $user): bool {
        return $user->id === $this->user_id && $this->created_at->addMinutes(30)->isFuture();
    }
    public function attachments()
    {
        return $this->hasMany(AnswerAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AnswerComment::class);
    }
}
