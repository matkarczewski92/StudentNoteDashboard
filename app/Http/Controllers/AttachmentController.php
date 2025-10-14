<?php

namespace App\Http\Controllers;

use App\Models\{NoteAttachment, LecturerMailAttachment};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    public function note(NoteAttachment $att)
    {
        $this->middleware('auth');
        // Optionally: check hidden note visibility if needed
        $mime = $att->mime_type ?: 'application/octet-stream';
        return Storage::disk('public')->response($att->path, $att->original_name, [ 'Content-Type' => $mime ]);
    }

    public function lecturer(LecturerMailAttachment $att)
    {
        $this->middleware('auth');
        $mail = $att->mail()->with('groups')->first();
        $user = auth()->user();
        if ($mail && !$mail->is_for_all && $mail->groups->count()) {
            $allowed = $user && (in_array($user->role ?? 'user', ['admin','moderator'], true)
                || $user->groups()->whereIn('groups.id', $mail->groups->pluck('id'))->exists());
            abort_unless($allowed, 403);
        }
        $mime = $att->mime_type ?: 'application/octet-stream';
        return Storage::disk('public')->response($att->path, $att->original_name, [ 'Content-Type' => $mime ]);
    }
}

