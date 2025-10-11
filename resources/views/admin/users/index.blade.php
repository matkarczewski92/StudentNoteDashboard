@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Użytkownicy</h1>

  @include('admin.users._flash')
  <form class="row g-2 mb-3">
    <div class="col-md-5">
      <input class="form-control" name="q" value="{{ $q }}" placeholder="Szukaj po imieniu, mailu, albumie">
    </div>
    <div class="col-md-4">
      <select class="form-select" name="group_id" onchange="this.form.submit()">
        <option value="">— Wszyscy —</option>
        @isset($groups)
          @foreach($groups as $g)
            <option value="{{ $g->id }}" {{ (int)($groupId ?? 0) === (int)$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
          @endforeach
        @endisset
      </select>
    </div>
    <div class="col-md-3 text-end">
      <button class="btn btn-outline-secondary">Szukaj</button>
    </div>
  </form>

  @if(isset($groups))
  <form method="post" action="{{ route('admin.users.bulk_add') }}" class="mb-3 d-flex gap-2 align-items-end">
    @csrf
    <input type="hidden" name="q" value="{{ $q }}">
    <input type="hidden" name="filter_group_id" value="{{ $groupId }}">
    <div>
      <label class="form-label">Dodaj wszystkich z tej listy do grupy</label>
      <select class="form-select" name="target_group_id" required>
        @foreach($groups as $g)
          <option value="{{ $g->id }}">{{ $g->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <button class="btn btn-primary">Dodaj</button>
    </div>
  </form>
  @endif

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>Imię i nazwisko</th>
          <th>Email</th>
          <th>Album</th>
          <th>Rola</th>
          <th>Grupy</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->album }}</td>
            <td>{{ $u->role ?? 'user' }}</td>
            <td>{{ $u->groups->pluck('name')->join(', ') }}</td>
            <td class="text-end">
              <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-secondary btn-sm">Edytuj</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
