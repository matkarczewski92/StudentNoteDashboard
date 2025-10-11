@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Przedmioty</h1>

  <form class="row g-2 align-items-end mb-3" method="get">
    <div class="col-md-6">
      <label class="form-label">Semestr</label>
      <select name="semester_id" class="form-select" onchange="this.form.submit()">
        @foreach($semesters as $s)
          <option value="{{ $s->id }}" {{ (int)$semesterId === (int)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
      </select>
    </div>
  </form>

  <div class="card mb-3">
    <div class="card-body">
      <form method="post" action="{{ route('admin.subjects.store') }}" class="row g-2">
        @csrf
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <div class="col-md-6">
          <label class="form-label">Nazwa (np. Wykłady: Psychologia)</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Kod</label>
          <input name="code" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Prowadzący</label>
          <input name="lecturer" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">Opis</label>
          <input name="description" class="form-control">
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary">Dodaj</button>
        </div>
      </form>
    </div>
  </div>

  <div class="vstack gap-2">
    @foreach($items as $sub)
      <div class="card">
        <div class="card-body">
          <form method="post" action="{{ route('admin.subjects.update', $sub) }}" class="row g-2 align-items-end">
            @csrf @method('put')
            <div class="col-md-5"><input name="name" class="form-control" value="{{ $sub->name }}"></div>
            <div class="col-md-2"><input name="code" class="form-control" value="{{ $sub->code }}" placeholder="Kod"></div>
            <div class="col-md-3"><input name="lecturer" class="form-control" value="{{ $sub->lecturer }}" placeholder="Prowadzący"></div>
            <div class="col-md-10"><input name="description" class="form-control" value="{{ $sub->description }}" placeholder="Opis"></div>
            <div class="col-md-2 text-end">
              <button class="btn btn-outline-secondary btn-sm">Zapisz</button>
            </div>
          </form>
          <div class="text-end mt-2">
            <form method="post" action="{{ route('admin.subjects.destroy', $sub) }}" onsubmit="return confirm('Usunąć przedmiot?');" class="d-inline">
              @csrf @method('delete')
              <button class="btn btn-outline-danger btn-sm">Usuń</button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection

