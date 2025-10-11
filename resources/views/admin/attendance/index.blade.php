@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Lista obecności</h1>
  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="get" action="{{ route('attendance.print') }}" target="_blank" class="row g-3 align-items-end">
        @php($isStaff = in_array(auth()->user()->role ?? 'user', ['admin','moderator'], true))
        <div class="col-md-6">
          <label class="form-label">Wybierz grupę</label>
          <select name="group_id" class="form-select" required>
            @foreach($groups as $g)
              <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->users_count }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 text-end">
          <button class="btn btn-primary">Otwórz wersję do druku</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
