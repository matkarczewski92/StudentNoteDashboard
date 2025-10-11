<?php

namespace App\Http\Controllers;

use App\Models\{Note, NoteComment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteCommentController extends Controller
{
    public function store(Request $request, Note $note)
    {
        $data = $request->validate([
            'body' => ['required','string','min:2'],
            'parent_id' => ['nullable','integer','exists:note_comments,id'],
        ], [
            'body.required' => 'Komentarz jest wymagany.',
            'body.min'      => 'Komentarz musi mieć co najmniej 2 znaki.',
        ]);

        // Jeśli to odpowiedź, upewnij się że parent należy do tej samej notatki
        if (!empty($data['parent_id'])) {
            $parent = NoteComment::find($data['parent_id']);
            if (!$parent || $parent->note_id !== $note->id) {
                return back()->withErrors(['body' => 'Nieprawidłowy komentarz nadrzędny.'])->withInput();
            }
        }

        NoteComment::create([
            'note_id'   => $note->id,
            'user_id'   => Auth::id(),
            'body'      => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
        return back()->with('ok', 'Dodano komentarz.');
    }

    public function destroy(NoteComment $comment)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $canOwner = $comment->user_id === $user->id && $comment->created_at->addHour()->isFuture();
        if (!($canOwner || in_array($user->role, ['admin','moderator'], true))) {
            abort(403);
        }
        $comment->delete();
        return back()->with('ok','Usunięto komentarz.');
    }
}
