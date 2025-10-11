@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h4 fw-bold mb-3">Grupy</h1>

  <div class="card mb-3">
    <div class="card-body">
      <form method="post" action="{{ route('admin.groups.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-8">
          <label class="form-label">Nazwa</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-4 text-end">
          <button class="btn btn-primary">Dodaj</button>
        </div>
      </form>
    </div>
  </div>

  <div class="vstack gap-2">
    @foreach($items as $g)
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <form method="post" action="{{ route('admin.groups.update', $g) }}" class="d-flex gap-2 align-items-center w-100">
            @csrf @method('put')
            <input name="name" class="form-control" value="{{ $g->name }}">
            <button class="btn btn-outline-secondary btn-sm">Zapisz</button>
          </form>
          <form method="post" action="{{ route('admin.groups.destroy', $g) }}" onsubmit="return confirm('Usunąć grupę?');">
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

