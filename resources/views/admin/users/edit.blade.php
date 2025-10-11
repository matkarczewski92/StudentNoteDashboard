@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Edycja użytkownika</h1>

  @include('admin.users._flash')
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="{{ route('admin.users.update', $user) }}" class="vstack gap-3">
        @csrf @method('put')
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Imię i nazwisko</label>
            <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Album</label>
            <input name="album" class="form-control" value="{{ old('album', $user->album) }}">
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Rola</label>
            <select name="role" class="form-select">
              @foreach(['user'=>'Użytkownik','moderator'=>'Moderator','admin'=>'Administrator'] as $val=>$label)
                <option value="{{ $val }}" {{ old('role', $user->role ?? 'user') === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Grupy</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach($groups as $g)
                <label class="form-check-label border rounded p-2">
                  <input type="checkbox" class="form-check-input me-1" name="groups[]" value="{{ $g->id }}" {{ in_array($g->id, $userGroupIds, true) ? 'checked' : '' }}>
                  {{ $g->name }}
                </label>
              @endforeach
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <button class="btn btn-primary">Zapisz</button>
          <form method="post" action="{{ route('admin.users.reset', $user) }}" onsubmit="return confirm('Wygenerować nowe hasło tymczasowe?');">
            @csrf
            <button class="btn btn-outline-warning">Reset hasła (tymczasowe)</button>
          </form>
        </div>
      </form>
    </div>
  </div>

  @if(auth()->id() !== $user->id && ($user->role ?? 'user') !== 'admin')
  <div class="card border-danger">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-semibold text-danger">Usuń konto użytkownika</div>
        <div class="small text-body-secondary">Operacja nieodwracalna.</div>
      </div>
      <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Na pewno trwale usunąć konto?');">
        @csrf @method('delete')
        <button class="btn btn-outline-danger btn-sm">Usuń konto</button>
      </form>
    </div>
  </div>
  @endif

  <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Powrót</a>
</div>
@endsection
