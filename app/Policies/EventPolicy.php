<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // każdy zalogowany
    }

    public function view(User $user, Event $event): bool
    {
        return true; // widok dostępny dla wszystkich zalogowanych
    }

    public function create(User $user): bool
    {
        return $user !== null; // każdy zalogowany
    }

    public function update(User $user, Event $event): bool
    {
        return $event->user_id === $user->id || in_array($user->role, ['admin','moderator'], true);
    }

    public function delete(User $user, Event $event): bool
    {
        return $event->user_id === $user->id || in_array($user->role, ['admin','moderator'], true);
    }

}
