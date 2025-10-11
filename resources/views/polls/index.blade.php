@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  <h1 class="h2 fw-bold mb-4">GĹ‚osowania</h1>

  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="{{ route('polls.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">TytuĹ‚ ankiety</label>
          <input name="title" class="form-control form-control-lg" placeholder="Np. Jaki termin zajÄ™Ä‡ pasuje?" required>
        </div>
        <div id="opts" class="mb-3 vstack gap-2">
          <input name="options[]" class="form-control" placeholder="Opcja 1" required>
          <input name="options[]" class="form-control" placeholder="Opcja 2" required>
        </div>
        @php $myGroups = auth()->user()?->groups()->orderBy('name')->get() ?? collect(); @endphp
        @if($myGroups->count())
        <div class="mb-3 d-flex align-items-center gap-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="p_only_group" name="only_group" value="1">
            <label class="form-check-label" for="p_only_group">Widoczne tylko dla mojej grupy</label>
          </div>
          <select name="group_id" id="p_group_id" class="form-select" style="max-width: 320px" disabled>
            @foreach($myGroups as $g)
              <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
          </select>
        </div>
        @push('scripts')
        <script>document.getElementById('p_only_group')?.addEventListener('change',e=>{document.getElementById('p_group_id').disabled=!e.target.checked;});</script>
        @endpush
        @endif
        <div class="d-flex align-items-center justify-content-between">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="is_multiple" name="is_multiple">
            <label class="form-check-label" for="is_multiple">PozwĂłl na wielokrotny wybĂłr</label>
          </div>
          <div class="d-flex gap-2">
            <button type="button" id="addOpt" class="btn btn-outline-secondary btn-sm">Dodaj opcjÄ™</button>
            <button class="btn btn-primary">UtwĂłrz ankietÄ™</button>
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
            <div class=" small text-body-secondary\>Głosy: {{ $p->votes_count }} @if($p->group)<span class=badge text-bg-warning text-dark ms-2>Grupa: {{ $p->group->name }}</span>@endif</div>
          </div>
          <a class="btn btn-outline-secondary btn-sm" href="{{ route('polls.show', $p) }}">OtwĂłrz</a>
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



