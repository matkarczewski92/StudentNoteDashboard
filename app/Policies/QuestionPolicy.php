<?php

namespace App\Policies;

use App\Models\{Question, User};

class QuestionPolicy
{
    public function update(User $user, Question $question): bool
    {
        return $question->canEditBy($user) || $user->hasAnyRole(['admin','moderator']);
    }

    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->user_id || $user->hasAnyRole(['admin','moderator']);
    }
}
