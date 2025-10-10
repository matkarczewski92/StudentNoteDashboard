<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    // $table niepotrzebne — "semesters" to domyślna nazwa
    protected $fillable = ['name', 'starts_at', 'ends_at'];

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class); // FK: semester_id
    }
}
