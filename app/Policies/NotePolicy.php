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
        return true; // widoczne dla wszystkich zalogowanych
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Note $note): bool
    {
        // Autor może edytować w ciągu 1h; moder/admin zawsze
        if (in_array($user->role, ['admin','moderator'], true)) return true;
        return $note->user_id === $user->id && $note->created_at->addHour()->isFuture();
    }

    public function delete(User $user, Note $note): bool
    {
        // Autor może usunąć w ciągu 1h; admin zawsze
        if ($user->role === 'admin') return true;
        return $note->user_id === $user->id && $note->created_at->addHour()->isFuture();
    }

    public function toggleHide(User $user, Note $note): bool
    {
        return in_array($user->role, ['admin','moderator'], true);
    }
}
