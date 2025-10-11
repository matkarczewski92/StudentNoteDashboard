@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Semestry</h1>

  <div class="card mb-3">
    <div class="card-body">
      <form method="post" action="{{ route('admin.semesters.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Nazwa</label>
          <input name="name" class="form-control" placeholder="np. Semestr 1" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Od</label>
          <input type="date" name="starts_at" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Do</label>
          <input type="date" name="ends_at" class="form-control">
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-primary">Dodaj</button>
        </div>
      </form>
    </div>
  </div>

  <div class="vstack gap-2">
    @foreach($items as $s)
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <form method="post" action="{{ route('admin.semesters.update', $s) }}" class="d-flex gap-2 align-items-center w-100">
            @csrf @method('put')
            <input name="name" class="form-control" value="{{ $s->name }}">
            <input type="date" name="starts_at" class="form-control" value="{{ $s->starts_at }}">
            <input type="date" name="ends_at" class="form-control" value="{{ $s->ends_at }}">
            <button class="btn btn-outline-secondary btn-sm">Zapisz</button>
          </form>
          <form method="post" action="{{ route('admin.semesters.destroy', $s) }}" onsubmit="return confirm('Usunąć semestr?');">
            @csrf @method('delete')
            <button class="btn btn-outline-danger btn-sm">Usuń</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection

