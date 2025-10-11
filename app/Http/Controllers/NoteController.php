<?php

namespace App\Http\Controllers;

use App\Models\{Note, NoteAttachment, NoteVote, NoteComment, Semester, Subject};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('id')->get();
        $selectedSemesterId = (int) $request->query('semester_id', optional($semesters->first())->id);
        $subjects = Subject::where('semester_id', $selectedSemesterId)->orderBy('name')->get();
        $selectedSubjectId = (int) $request->query('subject_id', 0);

        $notes = collect();
        if ($selectedSubjectId) {
            $notes = Note::with(['user.groups','subject'])
                ->where('subject_id', $selectedSubjectId)
                ->where('is_hidden', false)
                ->orderByDesc('lecture_date')
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('notes.index', [
            'semesters' => $semesters,
            'subjects'  => $subjects,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedSubjectId'  => $selectedSubjectId,
            'notes' => $notes,
        ]);
    }

    public function show(Note $note)
    {
        $note->load(['user.groups','subject','attachments'])
             ->loadCount([
                 'votes as up'   => fn ($q) => $q->where('value', 1),
                 'votes as down' => fn ($q) => $q->where('value', -1),
             ]);
        $comments = $note->comments()
            ->whereNull('parent_id')
            ->with(['user','replies.user'])
            ->latest()
            ->get();
        return view('notes.show', compact('note','comments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required','integer','exists:subjects,id'],
            'title'      => ['required','string','max:255'],
            'body'       => ['nullable','string'],
            'lecture_date' => ['nullable','date'],
            'attachments.*' => ['nullable','file','max:10240','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        $kind = $subject->inferredKind();
        $note = Note::create($data + ['user_id' => Auth::id(), 'kind' => $kind]);

        // save attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('notes', 'public');
                NoteAttachment::create([
                    'note_id'       => $note->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return redirect()->route('notes.show', $note)->with('ok','Notatka dodana.');
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);
        $data = $request->validate([
            'title'      => ['required','string','max:255'],
            'body'       => ['nullable','string'],
            'lecture_date' => ['nullable','date'],
            'attachments.*' => ['nullable','file','max:10240','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
            'remove_attachments'   => ['array'],
            'remove_attachments.*' => ['integer','exists:note_attachments,id'],
        ]);

        // uaktualnij kind wg nazwy przedmiotu (przedmiot nie zmienia się w edycji)
        $note->update($data + ['kind' => $note->subject?->inferredKind() ?? $note->kind]);

        // remove selected attachments
        $removeIds = $request->input('remove_attachments', []);
        if (!empty($removeIds)) {
            $toDelete = $note->attachments()->whereIn('id', $removeIds)->get();
            foreach ($toDelete as $att) {
                Storage::disk('public')->delete($att->path);
                $att->delete();
            }
        }
        // add new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('notes', 'public');
                NoteAttachment::create([
                    'note_id'       => $note->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return back()->with('ok','Zaktualizowano notatkę.');
    }

    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);
        foreach ($note->attachments as $att) {
            Storage::disk('public')->delete($att->path);
        }
        $note->delete();
        return redirect()->route('notes.index', [
            'semester_id' => optional($note->subject->semester ?? null)->id,
            'subject_id'  => $note->subject_id,
        ])->with('ok','Usunięto notatkę.');
    }

    public function vote(Request $request, Note $note)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Wymagane logowanie'], 401);
            }
            return redirect()->route('login');
        }
        $request->validate(['value' => ['required', Rule::in([-1,1])]]);
        NoteVote::updateOrCreate(
            ['note_id' => $note->id, 'user_id' => Auth::id()],
            ['value'   => (int) $request->input('value')]
        );

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $note->loadCount([
                'votes as up'   => fn ($q) => $q->where('value', 1),
                'votes as down' => fn ($q) => $q->where('value', -1),
            ]);
            return response()->json([
                'up' => (int) $note->up,
                'down' => (int) $note->down,
            ]);
        }
        return back();
    }

    public function toggleHide(Note $note)
    {
        $this->authorize('toggleHide', $note);
        $note->update(['is_hidden' => !$note->is_hidden]);
        return back()->with('ok', $note->is_hidden ? 'Ukryto notatkę.' : 'Odkryto notatkę.');
    }
}
