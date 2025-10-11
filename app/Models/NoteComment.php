<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class NoteComment extends Model
{
    protected $fillable = ['note_id','user_id','body','parent_id'];

    public function note(): BelongsTo { return $this->belongsTo(Note::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(self::class, 'parent_id')->oldest(); }
}
