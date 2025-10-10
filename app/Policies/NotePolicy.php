<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // każdy zalogowany widzi swoje; moder/admin wszystkie (logika w zapytaniu)
    }

    public function view(User $user, Note $note): bool
    {
        return $note->user_id === $user->id || in_array($user->role, ['admin','moderator'], true);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Note $note): bool
    {
        return $note->user_id === $user->id || in_array($user->role, ['admin','moderator'], true);
    }

    public function delete(User $user, Note $note): bool
    {
        return $note->user_id === $user->id || $user->role === 'admin';
    }

    public function toggleHide(User $user, Note $note): bool
    {
        return in_array($user->role, ['admin','moderator'], true);
    }
}
