<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class LecturerMail extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'title', 'body'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function attachments(): HasMany { return $this->hasMany(LecturerMailAttachment::class); }
    public function groups(): BelongsToMany { return $this->belongsToMany(Group::class, 'lecturer_mail_group'); }
}
