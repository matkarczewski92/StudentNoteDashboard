<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Semester, Subject, Group};

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        // Semestr
        $sem = Semester::firstOrCreate(['name' => 'Semestr 1'], [
            'starts_at' => now()->startOfYear()->format('Y-m-d'),
            'ends_at'   => now()->endOfYear()->format('Y-m-d'),
        ]);

        // Przedmioty (oddzielnie Wykłady/Ćwiczenia zgodnie z założeniem)
        Subject::firstOrCreate([
            'semester_id' => $sem->id,
            'name' => 'Wykłady: Psychologia ogólna',
        ], [
            'code' => 'PSY-LEC-1',
            'lecturer' => '—',
        ]);
        Subject::firstOrCreate([
            'semester_id' => $sem->id,
            'name' => 'Ćwiczenia: Psychologia ogólna',
        ], [
            'code' => 'PSY-EX-1',
            'lecturer' => '—',
        ]);

        // Grupa przykładowa
        Group::firstOrCreate(['name' => 'Grupa A']);
    }
}

