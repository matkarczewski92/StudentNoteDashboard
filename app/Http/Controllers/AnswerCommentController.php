<?php

namespace App\Http\Controllers;

use App\Models\{Answer, AnswerComment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerCommentController extends Controller
{
    public function store(Request $request, Answer $answer)
    {
        $data = $request->validate([
            'body' => ['required','string','min:2'],
            'parent_id' => ['nullable','integer','exists:answer_comments,id'],
        ], [
            'body.required' => 'Komentarz jest wymagany.',
            'body.min'      => 'Komentarz musi mieć co najmniej 2 znaki.',
        ]);

        if (!empty($data['parent_id'])) {
            $parent = AnswerComment::find($data['parent_id']);
            if (!$parent || $parent->answer_id !== $answer->id) {
                return back()->withErrors(['body' => 'Nieprawidłowy komentarz nadrzędny.'])->withInput();
            }
        }

        AnswerComment::create([
            'answer_id' => $answer->id,
            'user_id'   => Auth::id(),
            'body'      => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
        return back()->with('ok','Dodano komentarz.');
    }

    public function destroy(AnswerComment $comment)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $canOwner = $comment->user_id === $user->id && $comment->created_at->addHour()->isFuture();
        if (!($canOwner || in_array($user->role, ['admin','moderator'], true))) abort(403);
        $comment->delete();
        return back()->with('ok','Usunięto komentarz.');
    }
}

