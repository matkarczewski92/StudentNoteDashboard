@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Ostatnie pytania</h5>
          <div class="vstack gap-2">
            @forelse($latestQuestions as $q)
              <a class="text-decoration-none" href="{{ route('questions.show', $q) }}">
                <div class="small">{{ $q->title }}</div>
                <div class="small text-body-secondary">{{ $q->user->name ?? '—' }} • {{ $q->created_at->diffForHumans() }}</div>
              </a>
            @empty
              <div class="text-body-secondary small">Brak pytań.</div>
            @endforelse
          </div>
          <div class="mt-2 text-end"><a class="btn btn-outline-secondary btn-sm" href="{{ route('questions.index') }}">Zobacz wszystkie</a></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Nadchodzące wydarzenia (14 dni)</h5>
          <div class="vstack gap-2">
            @forelse($upcomingEvents as $e)
              <div>
                <span class="badge text-bg-warning">{{ $e->deadline?->format('Y-m-d H:i') }}</span>
                <span class="ms-1">{{ $e->title }}</span>
              </div>
            @empty
              <div class="text-body-secondary small">Brak wydarzeń.</div>
            @endforelse
          </div>
          <div class="mt-2 text-end"><a class="btn btn-outline-secondary btn-sm" href="{{ route('schedule.index') }}">Zobacz wszystkie</a></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Ostatnie notatki</h5>
          <div class="vstack gap-2">
            @forelse($latestNotes as $n)
              <a class="text-decoration-none" href="{{ route('notes.show', $n) }}">
                <div class="small">{{ $n->title }}</div>
                <div class="small text-body-secondary">{{ $n->subject->name ?? '—' }} • {{ $n->created_at->diffForHumans() }}</div>
              </a>
            @empty
              <div class="text-body-secondary small">Brak notatek.</div>
            @endforelse
          </div>
          <div class="mt-2 text-end"><a class="btn btn-outline-secondary btn-sm" href="{{ route('notes.index') }}">Zobacz wszystkie</a></div>
        </div>
      </div>
    </div>
  </div>

  @can('admin')
    <hr>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Szybkie skróty administracyjne</h5>
            <div class="vstack gap-1">
              <a class="link-body-emphasis" href="{{ route('admin.users.index') }}">Użytkownicy</a>
              <a class="link-body-emphasis" href="{{ route('admin.semesters.index') }}">Semestry</a>
              <a class="link-body-emphasis" href="{{ route('admin.subjects.index') }}">Przedmioty</a>
              <a class="link-body-emphasis" href="{{ route('admin.groups.index') }}">Grupy</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endcan
</div>
@endsection
