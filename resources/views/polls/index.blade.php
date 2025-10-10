@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  <h1 class="h2 fw-bold mb-4">Głosowania</h1>

  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="{{ route('polls.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Tytuł ankiety</label>
          <input name="title" class="form-control form-control-lg" placeholder="Np. Jaki termin zajęć pasuje?" required>
        </div>
        <div id="opts" class="mb-3 vstack gap-2">
          <input name="options[]" class="form-control" placeholder="Opcja 1" required>
          <input name="options[]" class="form-control" placeholder="Opcja 2" required>
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="is_multiple" name="is_multiple">
            <label class="form-check-label" for="is_multiple">Pozwól na wielokrotny wybór</label>
          </div>
          <div class="d-flex gap-2">
            <button type="button" id="addOpt" class="btn btn-outline-secondary btn-sm">Dodaj opcję</button>
            <button class="btn btn-primary">Utwórz ankietę</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="vstack gap-3">
    @foreach($polls as $p)
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <a class="link-body-emphasis text-decoration-none" href="{{ route('polls.show', $p) }}">
              <div class="fw-semibold">{{ $p->title }}</div>
            </a>
            <div class="small text-body-secondary">Głosów: {{ $p->votes_count }}</div>
          </div>
          <a class="btn btn-outline-secondary btn-sm" href="{{ route('polls.show', $p) }}">Otwórz</a>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $polls->links() }}
  </div>

</div>

@push('scripts')
<script>
  document.getElementById('addOpt')?.addEventListener('click', () => {
    const wrap = document.getElementById('opts');
    const i = document.createElement('input');
    i.name = 'options[]'; i.className = 'form-control'; i.placeholder = 'Nowa opcja';
    wrap.appendChild(i); i.focus();
  });
</script>
@endpush
@endsection

