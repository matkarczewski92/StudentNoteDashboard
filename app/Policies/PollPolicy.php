<?php

namespace App\Policies;

use App\Models\{Poll, User};
use Illuminate\Support\Facades\Gate;

class PollPolicy
{
    public function update(User $user, Poll $poll): bool
    {
        // Admin zawsze może
        if ($user->hasAnyRole(['admin'])) return true;

        // Jeśli oddano jakikolwiek głos — edycja zablokowana dla autora i moderatora
        $hasVotes = $poll->votes()->exists();
        if ($hasVotes) return false;

        // Moderator: może edytować bez limitu czasu (dopóki brak głosów)
        if ($user->hasAnyRole(['moderator'])) return true;

        // Autor: może edytować do 1h od utworzenia (dopóki brak głosów)
        if ($user->id === $poll->user_id) {
            return now()->lessThan($poll->created_at->copy()->addHour());
        }

        return false;
    }

    public function delete(User $user, Poll $poll): bool
    {
        // Admin: zawsze może usuwać
        if ($user->hasAnyRole(['admin'])) return true;

        // Moderator: nie może usuwać
        if ($user->hasAnyRole(['moderator'])) return false;

        // Autor: może usuwać przez 30 minut od utworzenia
        if ($user->id === $poll->user_id) {
            return now()->lte($poll->created_at->copy()->addMinutes(30));
        }

        return false;
    }

    public function close(User $user, Poll $poll): bool
    {
        // Zamknięcie/otwarcie dozwolone dla autora, moderatora i admina, niezależnie od głosów/czasu
        return $user->id === $poll->user_id || $user->hasAnyRole(['admin','moderator']);
    }
}
