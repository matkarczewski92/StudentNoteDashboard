@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  <h1 class="h2 fw-bold mb-4">Pytania</h1>

  {{-- FORMULARZ: pełna szerokość --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="{{ route('questions.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Szybkie pytanie</label>
          <input name="title" class="form-control form-control-lg" placeholder="Zadaj pytanie…" required>
        </div>
        <div class="text-end">
          <button class="btn btn-primary">Dodaj</button>
        </div>
      </form>
    </div>
  </div>

  {{-- LISTA PYTAŃ: układ 3-wierszowy --}}
  <div class="vstack gap-3">
    @foreach($questions as $q)
      <div class="card">
        <div class="card-body">

          {{-- Wiersz 1: autor + godzina --}}
          <div class="small fw-semibold text-truncate">
            {{ $q->user->name ?? '—' }}
            <span class="ms-2 fw-normal text-body-secondary">{{ $q->created_at->format('H:i') }}</span>
          </div>

          {{-- Wiersz 2: treść pytania (link) --}}
          <div class="mt-1">
            <a href="{{ route('questions.show', $q) }}" class="link-body-emphasis text-decoration-none">
              <div class="fs-6 text-wrap">{{ $q->title }}</div>
            </a>
          </div>

          {{-- Wiersz 3: meta po lewej, akcje po prawej --}}
          <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="d-flex align-items-center gap-3 small text-body-secondary">
              <span class="d-inline-flex align-items-center gap-1">
                <i class="bi bi-chat-left"></i> {{ $q->answers_count }}
              </span>
              @if($q->is_closed)
                <span class="badge text-bg-danger">Zamknięte</span>
              @else
                <span class="badge text-bg-success">Otwarte</span>
              @endif
            </div>

            <div class="d-flex gap-2">
              <a href="{{ route('questions.show', $q) }}#reply" class="btn btn-outline-secondary btn-sm">
                Napisz odpowiedź
              </a>
              <a href="{{ route('questions.show', $q) }}" class="btn btn-outline-secondary btn-sm">
                Otwórz
              </a>
            </div>
          </div>

        </div>
      </div>
    @endforeach
  </div>

  {{-- paginacja --}}
  <div class="mt-4">
    {{ $questions->links() }}
  </div>

</div>
@endsection
