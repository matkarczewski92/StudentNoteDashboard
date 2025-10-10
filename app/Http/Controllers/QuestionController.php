<?php

namespace App\Http\Controllers;

use App\Models\{Question, Answer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::withCount('answers')
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
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body'  => ['nullable','string'],
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
