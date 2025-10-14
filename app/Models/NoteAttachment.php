<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteAttachment extends Model
{
    protected $fillable = ['note_id','path','original_name','mime_type','size'];

    public function note(): BelongsTo { return $this->belongsTo(Note::class); }

    public function url(): string { return route('attachments.notes.show', $this); }
}
