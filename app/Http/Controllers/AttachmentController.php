<?php

namespace App\Http\Controllers;

use App\Models\{NoteAttachment, LecturerMailAttachment};
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function respondFile(string $diskPath, string $downloadName, string $mime, bool $inline = false)
    {
        $full = Storage::disk('public')->path($diskPath);
        abort_unless(is_file($full), 404);

        $headers = [
            'Content-Type' => $mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=86400',
        ];

        if ($inline) {
            // inline preview (images)
            return response()->file($full, $headers + ['Content-Disposition' => 'inline; filename="' . $downloadName . '"']);
        }
        // force download for non-images to avoid client/plugins mangling the file
        return response()->download($full, $downloadName, $headers);
    }

    public function note(NoteAttachment $att)
    {
        $mime = $att->mime_type ?: 'application/octet-stream';
        $isImage = str_starts_with(strtolower($mime), 'image/');
        return $this->respondFile($att->path, $att->original_name, $mime, $isImage);
    }

    public function lecturer(LecturerMailAttachment $att)
    {
        $mail = $att->mail()->with('groups')->first();
        $user = auth()->user();
        if ($mail && !$mail->is_for_all && $mail->groups->count()) {
            $allowed = $user && (in_array($user->role ?? 'user', ['admin','moderator'], true)
                || $user->groups()->whereIn('groups.id', $mail->groups->pluck('id'))->exists());
            abort_unless($allowed, 403);
        }
        $mime = $att->mime_type ?: 'application/octet-stream';
        $isImage = str_starts_with(strtolower($mime), 'image/');
        return $this->respondFile($att->path, $att->original_name, $mime, $isImage);
    }
}
