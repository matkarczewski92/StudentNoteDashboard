<nav class="navbar navbar-expand-md navbar-dark shadow-sm">
    <div class="container">
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
            <a class="nav-link me-3" href="{{ route('dashboard') }}"><i class="bi bi-journal-check"></i> Notatki</a> 
            <a class="nav-link me-3" href="{{ route('questions.index') }}"><i class="bi bi-cup-hot"></i> Tablica</a> 
            <a class="nav-link me-3" href="{{ route('polls.index') }}"><i class="bi bi-check2-square"></i> Głosowania</a> 
            

            @can('moderate')
            {{-- <a class="nav-link me-3" href="{{ route('dashboard') }}">Panel moderatora</a> --}}
            @endcan
            @can('admin')
            <a class="nav-link" href="{{ route('dashboard') }}">Panel admina</a>
            @endcan
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

                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                Wyloguj siÄ™
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


