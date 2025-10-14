<?php

namespace App\Providers;

use App\Models\Note;
use App\Policies\NotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

 
    protected $policies = [
    \App\Models\Event::class         => \App\Policies\EventPolicy::class,
    \App\Models\Question::class      => \App\Policies\QuestionPolicy::class,
    \App\Models\Answer::class        => \App\Policies\AnswerPolicy::class,
    \App\Models\Poll::class          => \App\Policies\PollPolicy::class,
    \App\Models\Note::class          => \App\Policies\NotePolicy::class,
    \App\Models\LecturerMail::class  => \App\Policies\LecturerMailPolicy::class,
    ];

    /**
     * Rejestracja wszelkich zasad autoryzacji aplikacji.
     */
    public function boot(): void
    {
        // 🔹 Uprawnienia globalne dla roli admina
        Gate::define('admin', fn($user) => $user->role === 'admin');

        // 🔹 Uprawnienia dla moderatora i admina
        Gate::define('moderate', fn($user) => in_array($user->role, ['admin', 'moderator'], true));

        // 🔹 Przykładowe uprawnienia do konkretnych sekcji w panelu
        Gate::define('manage-users', fn($user) => $user->role === 'admin');
        Gate::define('manage-notes', fn($user) => in_array($user->role, ['admin', 'moderator'], true));
    }
}
