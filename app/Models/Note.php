<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Note extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'title', 'body', 'kind', 'lecture_date', 'is_hidden'
    ];

    protected $casts = [
        'lecture_date' => 'date',
        'is_hidden'    => 'boolean',
    ];

    public function kindLabel(): string
    {
        return $this->kind === 'exercise' ? 'Ćwiczenia' : 'Wykłady';
    }

    public function kindBadgeClass(): string
    {
        return $this->kind === 'exercise' ? 'text-bg-danger' : 'text-bg-primary';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }

    public function attachments(): HasMany { return $this->hasMany(NoteAttachment::class); }
    public function comments(): HasMany { return $this->hasMany(NoteComment::class)->latest(); }
    public function votes(): HasMany { return $this->hasMany(NoteVote::class); }

    public function thumbsUpCount(): int  { return $this->votes()->where('value', 1)->count(); }
    public function thumbsDownCount(): int{ return $this->votes()->where('value', -1)->count(); }

    public function canEditBy(User $user): bool
    {
        return $user->id === $this->user_id && $this->created_at->addHour()->isFuture();
    }
}
