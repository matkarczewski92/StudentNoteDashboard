<?php

namespace App\Http\Controllers;

use App\Models\{LecturerMail, LecturerMailAttachment, Semester, Subject, Group};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LecturerMailController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('id')->get();
        $selectedSemesterId = (int) $request->query('semester_id', optional($semesters->first())->id);
        $subjects = Subject::where('semester_id', $selectedSemesterId)->orderBy('name')->get();
        $selectedSubjectId = (int) $request->query('subject_id', 0);

        $mails = collect();
        if ($selectedSubjectId) {
            $q = LecturerMail::with(['user.groups','subject','groups'])
                ->where('subject_id', $selectedSubjectId)
                ->orderByDesc('created_at');

            $user = $request->user();
            if ($user && !in_array($user->role ?? 'user', ['admin','moderator'], true)) {
                $userGroupIds = $user->groups()->pluck('groups.id')->all();
                $q->where(function($w) use ($userGroupIds) {
                    $w->where('is_for_all', true)
                      ->orWhereDoesntHave('groups');
                    if (!empty($userGroupIds)) {
                        $w->orWhereHas('groups', function($g) use ($userGroupIds) {
                            $g->whereIn('groups.id', $userGroupIds);
                        });
                    }
                });
            }

            $mails = $q->paginate(20);
        }

        $allGroups  = Group::orderBy('name')->get();
        $userGroups = $request->user()->groups()->orderBy('name')->get(['groups.id','groups.name']);

        return view('lecturers.index', [
            'semesters' => $semesters,
            'subjects'  => $subjects,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedSubjectId'  => $selectedSubjectId,
            'mails' => $mails,
            'allGroups' => $allGroups,
            'userGroups' => $userGroups,
        ]);
    }

    public function show(LecturerMail $mail)
    {
        $mail->load(['user.groups','subject','attachments','groups']);

        // access control for group-specific mails
        $user = Auth::user();
        if (!$mail->is_for_all && $mail->groups()->exists()) {
            $allowed = $user && (in_array($user->role ?? 'user', ['admin','moderator'], true)
                || $user->groups()->whereIn('groups.id', $mail->groups->pluck('id'))->exists());
            abort_unless($allowed, 403);
        }
        return view('lecturers.show', compact('mail'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required','integer','exists:subjects,id'],
            'title'      => ['required','string','max:255'],
            'body'       => ['nullable','string'],
            'group_ids'   => ['array'],
            'group_ids.*' => ['integer','exists:groups,id'],
            'attachments.*' => ['nullable','file','max:10240','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
        ]);

        $mail = LecturerMail::create([
            'user_id'    => Auth::id(),
            'subject_id' => (int)$data['subject_id'],
            'title'      => $data['title'],
            'body'       => $data['body'] ?? null,
            'is_for_all' => empty($data['group_ids'] ?? []),
        ]);

        // attach groups (public if none); non-admin/moderator can only choose own groups
        $selected = array_map('intval', $data['group_ids'] ?? []);
        if (!in_array($request->user()->role, ['admin', 'moderator'], true)) {
            $allowed = $request->user()->groups()->pluck('groups.id')->all();
            $selected = array_values(array_intersect($selected, $allowed));
        }
        $mail->groups()->sync($selected);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lecturer-mails', 'public');
                LecturerMailAttachment::create([
                    'lecturer_mail_id' => $mail->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return redirect()->route('lecturers.show', $mail)->with('ok','Dodano wiadomość.');
    }

    public function update(Request $request, LecturerMail $mail)
    {
        $this->authorize('update', $mail);
        $data = $request->validate([
            'title'      => ['required','string','max:255'],
            'body'       => ['nullable','string'],
            'group_ids'   => ['array'],
            'group_ids.*' => ['integer','exists:groups,id'],
            'attachments.*' => ['nullable','file','max:10240','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
            'remove_attachments'   => ['array'],
            'remove_attachments.*' => ['integer','exists:lecturer_mail_attachments,id'],
        ]);

        $mail->update([
            'title'      => $data['title'],
            'body'       => $data['body'] ?? null,
            'is_for_all' => empty($data['group_ids'] ?? []),
        ]);

        $selected = array_map('intval', $data['group_ids'] ?? []);
        if (!in_array($request->user()->role, ['admin', 'moderator'], true)) {
            $allowed = $request->user()->groups()->pluck('groups.id')->all();
            $selected = array_values(array_intersect($selected, $allowed));
        }
        $mail->groups()->sync($selected);

        $removeIds = $request->input('remove_attachments', []);
        if (!empty($removeIds)) {
            $toDelete = $mail->attachments()->whereIn('id', $removeIds)->get();
            foreach ($toDelete as $att) {
                Storage::disk('public')->delete($att->path);
                $att->delete();
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lecturer-mails', 'public');
                LecturerMailAttachment::create([
                    'lecturer_mail_id' => $mail->id,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => (int) $file->getSize(),
                ]);
            }
        }

        return back()->with('ok','Zaktualizowano.');
    }

    public function destroy(LecturerMail $mail)
    {
        $this->authorize('delete', $mail);
        foreach ($mail->attachments as $att) {
            Storage::disk('public')->delete($att->path);
        }
        $mail->delete();
        return redirect()->route('lecturers.index', [
            'semester_id' => optional($mail->subject->semester ?? null)->id,
            'subject_id'  => $mail->subject_id,
        ])->with('ok','Usunięto.');
    }
}
