<?php

namespace App\Http\Controllers;

use App\Models\{Question, Answer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with(['user'])
            ->withCount('answers')
            ->latest()
            ->paginate(20);

        return view('questions.index', compact('questions'));
    }

    public function show(Question $question)
    {
        $question->load(['user']);
        $answers = $question->answers()
            ->with(['user'])
            ->latest()
            ->paginate(20);

        return view('questions.show', compact('question','answers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body'  => ['nullable','string'],
        ]);

        $question = Question::create($data + ['user_id' => Auth::id()]);
        return redirect()->route('questions.show', $question)->with('ok','Pytanie dodane.');
    }

    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        // szybka ścieżka: sam toggle statusu
        if ($request->has('is_closed') && !$request->has('title') && !$request->has('body')) {
            $question->update(['is_closed' => (bool) $request->boolean('is_closed')]);
            return back()->with('ok', $question->is_closed ? 'Pytanie zamknięte.' : 'Pytanie otwarte.');
        }

        // pełna edycja pytania
        $data = $request->validate([
            'title'     => ['required','string','max:255'],
            'body'      => ['nullable','string'],
            'is_closed' => ['sometimes','boolean'],
        ]);

        $question->update($data);
        return back()->with('ok','Zaktualizowano pytanie.');
    }

    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);
        $question->delete();
        return redirect()->route('questions.index')->with('ok','Usunięto pytanie.');
    }
    
}
