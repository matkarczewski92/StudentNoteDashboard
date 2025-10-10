<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\AnswerAttachment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AnswerController extends Controller
{
    /**
     * Zapisz nową odpowiedź do pytania.
     */
    public function store(Request $request, Question $question)
    {
        

        $request->validate([
            'body'       => ['required', 'string', 'min:2'],
            'images.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        if ($question->is_closed) {
            return back()->withErrors(['body' => 'Pytanie jest zamknięte.']);
        }

        $answer = Answer::create([
            'question_id' => $question->id,
            'user_id'     => Auth::id(),
            'body'        => $request->string('body')->toString(),
        ]);

        // Załączniki (opcjonalnie wiele)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('answers', 'public');
                AnswerAttachment::create([
                    'answer_id'     => $answer->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return back()->with('ok', 'Odpowiedź dodana.');
    }

    /**
     * Edytuj istniejącą odpowiedź (treść + dodawanie/usuwanie załączników).
     */
    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        $request->validate([
            'body'                 => ['required', 'string', 'min:2'],
            'images.*'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
            'remove_attachments'   => ['array'],
            'remove_attachments.*' => ['integer', 'exists:answer_attachments,id'],
        ]);

        $answer->update(['body' => $request->string('body')->toString()]);

        // Usuń wybrane załączniki
        $removeIds = $request->input('remove_attachments', []);
        if (!empty($removeIds)) {
            $toDelete = $answer->attachments()->whereIn('id', $removeIds)->get();
            foreach ($toDelete as $att) {
                Storage::disk('public')->delete($att->path);
                $att->delete();
            }
        }

        // Dodaj nowe załączniki
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('answers', 'public');
                AnswerAttachment::create([
                    'answer_id'     => $answer->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return back()->with('ok', 'Zaktualizowano odpowiedź.');
    }

    /**
     * Usuń odpowiedź.
     */
    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);

        // Usuń pliki załączników z dysku
        foreach ($answer->attachments as $att) {
            Storage::disk('public')->delete($att->path);
        }

        $answer->delete();

        return back()->with('ok', 'Usunięto odpowiedź.');
    }

    /**
     * Głosowanie na odpowiedź: value = +1 (kciuk w górę) lub -1 (kciuk w dół).
     * Zwraca JSON dla żądań AJAX (Accept: application/json / X-Requested-With).
     */
    public function vote(Request $request, Answer $answer)
    {
        if (!Auth::check()) {
            // Dla AJAX – 401; dla zwykłego POSTa – redirect do login
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Wymagane logowanie'], 401);
            }
            return redirect()->route('login');
        }

        $request->validate([
            'value' => ['required', Rule::in([-1, 1])],
        ]);

        AnswerVote::updateOrCreate(
            ['answer_id' => $answer->id, 'user_id' => Auth::id()],
            ['value'     => (int) $request->input('value')]
        );

        // Dla AJAX – zwróć liczniki
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $answer->loadCount([
                'votes as up'   => fn ($q) => $q->where('value', 1),
                'votes as down' => fn ($q) => $q->where('value', -1),
            ]);

            return response()->json([
                'up'   => (int) $answer->up,
                'down' => (int) $answer->down,
            ]);
        }

        // Fallback – zwykły redirect
        return back();
    }
}
