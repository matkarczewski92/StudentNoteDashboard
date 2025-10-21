<?php

namespace App\Http\Controllers;

use App\Models\{Note, NoteAttachment, NoteVote, NoteComment, Semester, Subject, Group};
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
        $selectedSubject = $subjects->firstWhere('id', $selectedSubjectId);
        $selectedSubjectKind = $selectedSubject?->inferredKind();

        $groups = collect();
        $selectedGroupIds = [];
        if ($selectedSubject && $selectedSubjectKind === 'exercise') {
            $groups = Group::orderBy('name')->get();
            $requestedGroupIds = $request->query('group_ids', []);
            if (!is_array($requestedGroupIds)) {
                $requestedGroupIds = [$requestedGroupIds];
            }
            $selectedGroupIds = collect($requestedGroupIds)
                ->map(static fn ($id) => (int) $id)
                ->filter(static fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        $notes = collect();
        if ($selectedSubjectId) {
            $notesQuery = Note::with(['user.groups','subject'])
                ->where('subject_id', $selectedSubjectId)
                ->where('is_hidden', false);

            if ($selectedSubjectKind === 'exercise' && !empty($selectedGroupIds)) {
                $notesQuery->whereHas('user.groups', function ($q) use ($selectedGroupIds) {
                    $q->whereIn('groups.id', $selectedGroupIds);
                });
            }

            $notes = $notesQuery
                ->orderByDesc('lecture_date')
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('notes.index', [
            'semesters' => $semesters,
            'subjects'  => $subjects,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedSubjectId'  => $selectedSubjectId,
            'selectedSubjectKind' => $selectedSubjectKind,
            'groups' => $groups,
            'selectedGroupIds' => $selectedGroupIds,
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
            // allow up to 50MB, then enforce per-type limits below
            'attachments.*' => ['nullable','file','max:51200','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
        ]);

        // Per-type size limits: images up to 10MB, others up to 50MB
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) continue;
                $mime = $file->getMimeType();
                $size = (int) $file->getSize();
                if (str_starts_with($mime, 'image/') && $size > 10 * 1024 * 1024) {
                    return back()->withErrors(['attachments' => 'Zdjęcia mogą mieć maks. 10 MB.'])->withInput();
                }
                if (!str_starts_with($mime, 'image/') && $size > 50 * 1024 * 1024) {
                    return back()->withErrors(['attachments' => 'Pliki mogą mieć maks. 50 MB.'])->withInput();
                }
            }
        }

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
            'attachments.*' => ['nullable','file','max:51200','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
            'remove_attachments'   => ['array'],
            'remove_attachments.*' => ['integer','exists:note_attachments,id'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) continue;
                $mime = $file->getMimeType();
                $size = (int) $file->getSize();
                if (str_starts_with($mime, 'image/') && $size > 10 * 1024 * 1024) {
                    return back()->withErrors(['attachments' => 'Zdjęcia mogą mieć maks. 10 MB.'])->withInput();
                }
                if (!str_starts_with($mime, 'image/') && $size > 50 * 1024 * 1024) {
                    return back()->withErrors(['attachments' => 'Pliki mogą mieć maks. 50 MB.'])->withInput();
                }
            }
        }

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
