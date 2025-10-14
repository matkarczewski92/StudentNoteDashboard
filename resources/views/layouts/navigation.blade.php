<nav class="navbar navbar-expand-md navbar-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            StudentNoteDashboard <sup>beta v0.1</sup>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left -->
            <ul class="navbar-nav me-auto"></ul>
            <a class="nav-link me-3" href="{{ route('dashboard') }}"><i class="bi bi-house"></i> Home</a>
            <a class="nav-link me-3" href="{{ route('schedule.index') }}"><i class="bi bi-calendar-date"></i> Harmonogram</a>
            <a class="nav-link me-3" href="{{ route('notes.index') }}"><i class="bi bi-journal-check"></i> Notatki</a>
            @if (Route::has('lecturers.index'))
            <a class="nav-link me-3" href="{{ route('lecturers.index') }}"><i class="bi bi-envelope-paper"></i> Od wykładowców</a>
            @endif
            <a class="nav-link me-3" href="{{ route('questions.index') }}"><i class="bi bi-cup-hot"></i> Tablica</a>
            <a class="nav-link me-3" href="{{ route('polls.index') }}"><i class="bi bi-check2-square"></i> Głosowania</a>
            <a class="nav-link me-3" href="{{ route('attendance.index') }}"><i class="bi bi-people"></i> Lista obecności</a>

            @can('moderate')
            {{-- <a class="nav-link me-3" href="{{ route('dashboard') }}">Panel moderatora</a> --}}
            @endcan
            {{-- Admin link przeniesiony do dropdownu użytkownika --}}

            <!-- Right -->
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">

                        <a id="navbarDropdown"
                           class="nav-link dropdown-toggle"
                           href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                           {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                Edycja profilu
                            </a>
                            @auth
                            <a class="dropdown-item" href="{{ route('attendance.index') }}">Lista obecności</a>
                            @endauth
                            @can('admin')
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-item-text text-body-secondary small">Administrator</span>
                            <a class="dropdown-item" href="{{ route('admin.users.index') }}">Użytkownicy</a>
                            <a class="dropdown-item" href="{{ route('admin.semesters.index') }}">Semestry</a>
                            <a class="dropdown-item" href="{{ route('admin.subjects.index') }}">Przedmioty</a>
                            <a class="dropdown-item" href="{{ route('admin.groups.index') }}">Grupy</a>
                            @endcan

                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                Wyloguj się
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
