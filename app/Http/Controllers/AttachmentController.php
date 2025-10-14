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

    private function isImage(?string $mime, string $filename): bool
    {
        $m = strtolower($mime ?? '');
        if (str_starts_with($m, 'image/')) return true;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
    }

    private function respondFile(string $diskPath, string $downloadName, string $mime, bool $inline = false)
    {
        $fs = Storage::disk('public');
        $full = $fs->path($diskPath);
        abort_unless(is_file($full), 404);

        // Robust MIME detection
        $mime = $mime ?: ($fs->mimeType($diskPath) ?: 'application/octet-stream');
        if ($mime === 'application/octet-stream') {
            $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            $map = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
                'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            if (isset($map[$ext])) $mime = $map[$ext];
        }

        $headers = [ 'Cache-Control' => 'private, max-age=86400' ];

        if ($inline) {
            // For images, let Storage set headers and stream inline without Content-Disposition
            return Storage::disk('public')->response($diskPath, null, $headers + [
                'Content-Type' => $mime,
            ]);
        }
        return response()->download($full, $downloadName, $headers + [ 'Content-Type' => $mime ]);
    }

    public function note(NoteAttachment $att)
    {
        $fs = Storage::disk('public');
        $mime = $att->mime_type ?: ($fs->mimeType($att->path) ?: 'application/octet-stream');
        $isImage = $this->isImage($mime, $att->original_name);
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
        $fs = Storage::disk('public');
        $mime = $att->mime_type ?: ($fs->mimeType($att->path) ?: 'application/octet-stream');
        $isImage = $this->isImage($mime, $att->original_name);
        return $this->respondFile($att->path, $att->original_name, $mime, $isImage);
    }
}
