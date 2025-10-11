<?php

namespace App\Http\Controllers;

use App\Models\{Question, Answer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $q = Question::with(['user','group'])
            ->withCount('answers')
            ->latest();

        if ($user && !in_array($user->role ?? 'user', ['admin','moderator'], true)) {
            $groupIds = $user->groups()->pluck('groups.id')->all();
            $q->where(function($w) use ($groupIds) {
                $w->whereNull('group_id');
                if (!empty($groupIds)) { $w->orWhereIn('group_id', $groupIds); }
            });
        }

        $questions = $q->paginate(20);

        return view('questions.index', compact('questions'));
    }

    public function show(Question $question)
    {
        $question->load(['user','group']);
        $user = Auth::user();
        if ($question->group_id) {
            $allowed = $user && (in_array($user->role ?? 'user', ['admin','moderator'], true) || $user->groups()->where('groups.id',$question->group_id)->exists());
            abort_unless($allowed, 403);
        }
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
            'only_group' => ['sometimes','boolean'],
            'group_id' => ['nullable','integer','exists:groups,id'],
        ]);

        $groupId = null;
        if ($request->boolean('only_group') && !empty($data['group_id'])) {
            // dopuszczaj tylko grupy autora
            $isMine = Auth::user()?->groups()->where('groups.id', $data['group_id'])->exists();
            $groupId = $isMine ? (int)$data['group_id'] : null;
        }

        $question = Question::create([
            'user_id' => Auth::id(),
            'title'   => $data['title'],
            'body'    => $data['body'] ?? null,
            'group_id'=> $groupId,
        ]);
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
