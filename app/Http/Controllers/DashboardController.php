<?php

namespace App\Http\Controllers;

use App\Models\{Question, Event, Note};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $latestQuestions = Question::with('user')->latest()->take(3)->get();
        $upcomingEvents = Event::whereBetween('deadline', [now(), now()->addDays(14)])
            ->orderBy('deadline')->take(6)->get();
        $latestNotes = Note::with(['user','subject'])->latest()->take(5)->get();

        return view('dashboard', compact('latestQuestions','upcomingEvents','latestNotes'));
    }
}

