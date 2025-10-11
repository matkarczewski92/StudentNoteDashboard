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

    public function inferredKind(): string
    {
        $name = mb_strtolower($this->name ?? '');
        if (str_contains($name, 'ćwic') || str_contains($name, 'cwic')) {
            return 'exercise';
        }
        if (str_contains($name, 'wykł') || str_contains($name, 'wykl') || str_contains($name, 'wyklad')) {
            return 'lecture';
        }
        return 'lecture';
    }
}
