<?php

namespace App\Policies;

use App\Models\{Answer, User};

class AnswerPolicy
{
    public function update(User $user, Answer $answer): bool
    {
        return $answer->canEditBy($user) || $user->hasAnyRole(['admin','moderator']);
    }

    public function delete(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id || $user->hasAnyRole(['admin','moderator']);
    }
}
