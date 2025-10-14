@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between gap-3">
      <div class="flex-grow-1">
        <h1 class="h4 fw-bold mb-2">{{ $mail->title }}</h1>
        <div class="small text-body-secondary">
          <span class="ms-2">Przedmiot: {{ $mail->subject->name ?? '—' }}</span>
          <span class="ms-2">Autor: {{ $mail->user->name ?? '—' }} • {{ $mail->created_at->diffForHumans() }}</span>
          @php $groups = optional($mail->user)->groups?->pluck('name')->filter()->values() ?? collect(); @endphp
          @if($groups->count())
            <span class="ms-2">Grupa: {{ $groups->join(', ') }}</span>
          @endif
          @if($mail->groups && $mail->groups->count())
            <span class="badge text-bg-warning text-dark ms-2">Grupy: {{ $mail->groups->pluck('name')->join(', ') }}</span>
          @else
            <span class="badge text-bg-secondary ms-2">Widoczność: wszyscy</span>
          @endif
        </div>
      </div>
      <div class="text-end">
        <div class="d-flex justify-content-end gap-2">
          @can('delete', $mail)
          <form method="post" action="{{ route('lecturers.destroy', $mail) }}" onsubmit="return confirm('Usunąć wpis?');" class="d-inline">
            @csrf @method('delete')
            <button class="btn btn-outline-danger btn-sm">Usuń</button>
          </form>
          @endcan
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      @if($mail->body)
        <div class="mb-3">{!! $mail->body !!}</div>
      @endif
      @if($mail->attachments && $mail->attachments->count())
        <div class="small text-body-secondary mb-2">Załączniki:</div>
        <div class="d-flex flex-wrap gap-2">
          @foreach($mail->attachments as $att)
            @php $isImage = Str::startsWith($att->mime_type, 'image/'); @endphp
            @if($isImage)
              <a href="{{ $att->url() }}" target="_blank"><img src="{{ $att->url() }}" class="img-thumbnail" style="max-height:120px" alt="{{ $att->original_name }}" title="{{ $att->original_name }}"></a>
            @else
              <a href="{{ $att->url() }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-paperclip"></i> {{ $att->original_name }} ({{ number_format($att->size/1024, 0) }} KB)
              </a>
            @endif
          @endforeach
        </div>
      @endif
    </div>
  </div>

  @can('update', $mail)
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="{{ route('lecturers.update', $mail) }}" enctype="multipart/form-data" class="vstack gap-3">
        @csrf @method('put')
        <div>
          <label class="form-label">Tytuł</label>
          <input name="title" class="form-control" value="{{ old('title', $mail->title) }}" required>
        </div>
        <div>
          <label class="form-label">Dot. przedmiotu</label>
          <div class="form-control bg-body-secondary">{{ $mail->subject->name ?? '—' }}</div>
        </div>
        <div>
          <label class="form-label">Treść</label>
          <div class="d-flex gap-2 mb-2">
            <button class="btn btn-sm btn-outline-secondary js-bold" type="button"><i class="bi bi-type-bold"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-italic" type="button"><i class="bi bi-type-italic"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-ul" type="button"><i class="bi bi-list-ul"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-link" type="button"><i class="bi bi-link-45deg"></i></button>
          </div>
          <div id="editor" contenteditable="true" class="form-control" style="min-height:120px">{!! old('body', $mail->body) !!}</div>
          <input type="hidden" name="body" id="editorHidden" value="{{ old('body', $mail->body) }}">
        </div>
        @php $selected = collect(old('group_ids', $mail->groups->pluck('id')->all())); @endphp
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="edit-only-groups" {{ $selected->count() ? 'checked' : '' }}>
          <label class="form-check-label" for="edit-only-groups">Widoczne tylko dla wybranych grup</label>
        </div>
        <div id="edit-groups-wrap" class="mb-2 {{ $selected->count() ? '' : 'd-none' }}">
          <label class="form-label d-block">Dotyczy grup:</label>
          <div class="row row-cols-1 row-cols-sm-2 g-2">
            @php $groupsList = (auth()->user() && auth()->user()->can('moderate')) ? \App\Models\Group::orderBy('name')->get() : auth()->user()->groups()->orderBy('name')->get(['groups.id','groups.name']); @endphp
            @foreach($groupsList as $g)
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="group_ids[]" value="{{ $g->id }}" id="edit-g-{{ $g->id }}" @checked($selected->contains($g->id)) {{ $selected->count() ? '' : 'disabled' }}>
                  <label class="form-check-label" for="edit-g-{{ $g->id }}">{{ $g->name }}</label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
        @if($mail->attachments && $mail->attachments->count())
        <div>
          <label class="form-label">Usuń istniejące załączniki</label>
          <div class="d-flex flex-wrap gap-2">
            @foreach($mail->attachments as $att)
              <label class="border rounded p-2 form-check">
                <input class="form-check-input me-2" type="checkbox" name="remove_attachments[]" value="{{ $att->id }}">
                <span class="form-check-label">{{ $att->original_name }}</span>
              </label>
            @endforeach
          </div>
        </div>
        @endif
        <div>
          <label class="form-label">Dodaj nowe załączniki</label>
          <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
        </div>
        <div class="text-end">
          <button class="btn btn-primary btn-sm">Zapisz zmiany</button>
        </div>
      </form>
    </div>
  </div>
  @endcan

</div>
@endsection

@push('scripts')
<script>
  (function(){
    const ed = document.getElementById('editor');
    const hidden = document.getElementById('editorHidden');
    const form = ed?.closest('form');
    if (!ed || !form) return;
    function cmd(c, v=null){ document.execCommand(c, false, v); ed.focus(); }
    form.querySelector('.js-bold')?.addEventListener('click', ()=>cmd('bold'));
    form.querySelector('.js-italic')?.addEventListener('click', ()=>cmd('italic'));
    form.querySelector('.js-ul')?.addEventListener('click', ()=>cmd('insertUnorderedList'));
    form.querySelector('.js-link')?.addEventListener('click', ()=>{ const u = prompt('Adres URL'); if(u) cmd('createLink', u); });
    form.addEventListener('submit', ()=>{ hidden.value = ed.innerHTML.trim(); });
  })();

  (function(){
    const wrap = document.getElementById('edit-groups-wrap');
    const chk = document.getElementById('edit-only-groups');
    const inputs = wrap ? wrap.querySelectorAll('input[name="group_ids[]"]') : [];
    if (chk && wrap) {
      const apply = () => { wrap.classList.toggle('d-none', !chk.checked); inputs.forEach(i=> i.disabled = !chk.checked); };
      apply();
      chk.addEventListener('change', apply);
    }
  })();
</script>
@endpush
