<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    // $table niepotrzebne — "subjects" to domyślna nazwa
    protected $fillable = ['semester_id', 'name', 'lecturer', 'code', 'description'];


    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class); // belongs to one semester
    }
}
