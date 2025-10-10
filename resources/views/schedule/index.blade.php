@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
    // $firstDay dostarczony przez kontroler
    $prev = $firstDay->copy()->subMonth();
    $next = $firstDay->copy()->addMonth();
@endphp

<div class="container">

    {{-- Pasek nawigacji miesiącami + przycisk "Dodaj" --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a class="btn btn-outline-secondary"
           href="{{ route('schedule.index', ['year' => $prev->year, 'month' => $prev->month]) }}">
            &laquo; {{ $prev->isoFormat('MMMM YYYY') }}
        </a>

        <h3 class="m-0">{{ $firstDay->isoFormat('MMMM YYYY') }}</h3>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary"
               href="{{ route('schedule.index', ['year' => $next->year, 'month' => $next->month]) }}">
                {{ $next->isoFormat('MMMM YYYY') }} &raquo;
            </a>

            {{-- Przycisk otwierający modal dodawania --}}
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                Dodaj
            </button>
        </div>
    </div>

    {{-- KALENDARZ --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>pon</th>
                            <th>wto</th>
                            <th>śro</th>
                            <th>czw</th>
                            <th>pią</th>
                            <th>sob</th>
                            <th>nie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weeks as $week)
                            <tr>
                                @foreach ($week as $day)
                                    @if ($day)
                                        @php
                                            $isToday = $day->isToday();
                                        @endphp
                                        <td class="{{ $isToday ? 'table-primary' : '' }}" style="height: 80px;">
                                            <div class="fw-semibold">{{ $day->day }}</div>
                                        </td>
                                    @else
                                        <td class="bg-light" style="height: 80px;"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- LISTA WYDARZEŃ W MIESIĄCU --}}
    <div class="card">
        <div class="card-header">Wydarzenia w tym miesiącu</div>
        <div class="card-body p-0">
            @forelse ($events->groupBy(fn($e) => $e->deadline->toDateString()) as $date => $items)
                <div class="border-bottom p-3">
                    <h6 class="text-muted mb-2">
                        {{ Carbon::parse($date)->locale('pl')->isoFormat('dddd, D MMMM YYYY') }}
                    </h6>
                    <ul class="mb-0">
                        @foreach ($items as $e)
                            <li>
                                <strong>{{ $e->title }}</strong>
                                — {{ $e->deadline->format('H:i') }}
                                @if ($e->groups->isNotEmpty())
                                    <span class="text-muted">
                                        (GRUPY: {{ $e->groups->pluck('name')->join(', ') }})
                                    </span>
                                @endif

                                @can('update', $e)
                                    <button class="btn btn-sm btn-outline-secondary ms-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editEventModal-{{ $e->id }}">
                                        ✏️
                                    </button>
                                @endcan

                                @can('delete', $e)
                                    <form action="{{ route('schedule.destroy', $e) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Na pewno usunąć to wydarzenie?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                    </form>
                                @endcan

                                @if ($e->description)
                                    <div class="small text-muted">{{ $e->description }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="p-3 text-muted">Brak wydarzeń w tym miesiącu.</div>
            @endforelse
        </div>
    </div>
</div>

@foreach ($events as $eventForModal)
    @can('update', $eventForModal)
    <div class="modal fade" id="editEventModal-{{ $eventForModal->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <form method="POST" action="{{ route('schedule.update', $eventForModal) }}">
            @csrf
            @method('PUT')

            <div class="modal-header">
              <h5 class="modal-title">Edytuj wydarzenie</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Tytuł</label>
                <input type="text" name="title"
                       value="{{ old('title', $eventForModal->title) }}"
                       class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Termin (data i godzina)</label>
                <input type="datetime-local" name="deadline"
                       value="{{ old('deadline', $eventForModal->deadline->format('Y-m-d\TH:i')) }}"
                       class="form-control" required>
              </div>
            @php $selected = collect(old('group_ids', $eventForModal->groups->pluck('id')->all())); @endphp
            <div class="mb-3">
            <label class="form-label d-block">Dotyczy grup:</label>
            <div class="row row-cols-1 row-cols-sm-2 g-2">
                @foreach($allGroups as $g)
                <div class="col">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                            id="edit-{{ $eventForModal->id }}-g-{{ $g->id }}"
                            name="group_ids[]" value="{{ $g->id }}"
                            @checked($selected->contains($g->id))>
                    <label class="form-check-label" for="edit-{{ $eventForModal->id }}-g-{{ $g->id }}">{{ $g->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>
            </div>
              <div class="mb-1">
                <label class="form-label">Opis</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $eventForModal->description) }}</textarea>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button>
              <button type="submit" class="btn btn-primary">Zapisz</button>
            </div>

          </form>
        </div>
      </div>
    </div>
    @endcan
@endforeach


{{-- MODAL: Dodaj wydarzenie --}}
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="{{ route('schedule.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addEventModalLabel">Dodaj wydarzenie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        </div>

        <div class="modal-body">
          {{-- Tytuł --}}
          <div class="mb-3">
            <label class="form-label">Tytuł</label>
            <input type="text" name="title"
                   value="{{ old('title') }}"
                   class="form-control @error('title') is-invalid @enderror" required>
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Termin (deadline) --}}
          <div class="mb-3">
            <label class="form-label">Termin (data i godzina)</label>
            <input type="datetime-local" name="deadline"
                   value="{{ old('deadline') }}"
                   class="form-control @error('deadline') is-invalid @enderror" required>
            @error('deadline') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Grupy (multi-select) --}}
        <div class="mb-3">
        <label class="form-label d-block">Dotyczy grup:</label>
        <div class="row row-cols-1 row-cols-sm-2 g-2">
            @foreach($allGroups as $g)
            <div class="col">
                <div class="form-check">
                <input class="form-check-input" type="checkbox"
                        id="create-g-{{ $g->id }}"
                        name="group_ids[]" value="{{ $g->id }}"
                        @checked(collect(old('group_ids', []))->contains($g->id))>
                <label class="form-check-label" for="create-g-{{ $g->id }}">{{ $g->name }}</label>
                </div>
            </div>
            @endforeach
        </div>
        </div>

          {{-- Opis --}}
          <div class="mb-1">
            <label class="form-label">Opis</label>
            <textarea name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button>
          <button type="submit" class="btn btn-primary">Zapisz</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
