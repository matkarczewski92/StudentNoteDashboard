<?php

namespace App\Http\Controllers;

use App\Models\{Answer, AnswerVote, Question};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question)
    {
        $request->validate(['body' => ['required','string','min:2']]);
        if ($question->is_closed) {
            return back()->withErrors(['body' => 'Pytanie jest zamknięte.']);
        }
        Answer::create([
            'question_id' => $question->id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);
        return back()->with('ok','Odpowiedź dodana.');
    }

    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);
        $data = $request->validate(['body' => ['required','string','min:2']]);
        $answer->update($data);
        return back()->with('ok','Zaktualizowano odpowiedź.');
    }

    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);
        $answer->delete();
        return back()->with('ok','Usunięto odpowiedź.');
    }

    // Łapki w górę / dół
    public function vote(Request $request, Answer $answer)
    {
        $request->validate([
            'value' => ['required', Rule::in([-1, 1])],
        ]);

        AnswerVote::updateOrCreate(
            ['answer_id' => $answer->id, 'user_id' => Auth::id()],
            ['value' => (int)$request->value]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'up' => $answer->thumbsUpCount(),
                'down' => $answer->thumbsDownCount(),
            ]);
        }
        return back();
    }
}
