<?php

namespace App\Policies;

use App\Models\LecturerMail;
use App\Models\User;

class LecturerMailPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, LecturerMail $mail): bool { return true; }
    public function create(User $user): bool { return true; }

    public function update(User $user, LecturerMail $mail): bool
    {
        if (in_array($user->role, ['admin','moderator'], true)) return true;
        return $mail->user_id === $user->id && $mail->created_at->addHour()->isFuture();
    }

    public function delete(User $user, LecturerMail $mail): bool
    {
        if (in_array($user->role, ['admin','moderator'], true)) return true;
        return $mail->user_id === $user->id && $mail->created_at->addHour()->isFuture();
    }
}

