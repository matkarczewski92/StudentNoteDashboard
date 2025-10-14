<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturerMailAttachment extends Model
{
    protected $fillable = ['lecturer_mail_id','path','original_name','mime_type','size'];

    public function mail(): BelongsTo { return $this->belongsTo(LecturerMail::class, 'lecturer_mail_id'); }

    public function url(): string { return route('attachments.lecturers.show', $this); }
}
