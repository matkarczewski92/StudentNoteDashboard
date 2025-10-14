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

        $size = (int) $fs->size($diskPath);
        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => (string) $size,
            'Cache-Control' => 'private, max-age=86400',
            'Accept-Ranges' => 'bytes',
        ];

        // Stream to client (less memory, fewer host issues)
        $stream = $fs->readStream($diskPath);
        abort_unless(is_resource($stream), 404);

        $disposition = ($inline ? 'inline' : 'attachment') . '; filename="' . $downloadName . '"';
        $headers['Content-Disposition'] = $disposition;

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, $headers);
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
