@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between gap-3">
      <div class="flex-grow-1">
        <h1 class="h4 fw-bold mb-2">{{ $note->title }}</h1>
        <div class="small text-body-secondary">
          @if($note->lecture_date)
            <span class="badge text-bg-secondary">{{ $note->lecture_date->format('Y-m-d') }}</span>
          @endif
          <span class="badge {{ $note->kindBadgeClass() }} ms-1">{{ $note->kindLabel() }}</span>
          <span class="ms-2">Przedmiot: {{ $note->subject->name ?? '—' }}</span>
          <span class="ms-2">Autor: {{ $note->user->name ?? '—' }} • {{ $note->created_at->diffForHumans() }}</span>
          @php $groups = optional($note->user)->groups?->pluck('name')->filter()->values() ?? collect(); @endphp
          @if($groups->count())
            <span class="ms-2">Grupa: {{ $groups->join(', ') }}</span>
          @endif
        </div>
      </div>
      <div class="text-end">
        <div class="d-flex justify-content-end gap-2">
          @can('toggleHide', $note)
          <form method="post" action="{{ route('notes.toggle', $note) }}" class="d-inline">
            @csrf @method('patch')
            <button class="btn btn-outline-secondary btn-sm">{{ $note->is_hidden ? 'Odkryj' : 'Ukryj' }}</button>
          </form>
          @endcan
          @can('delete', $note)
          <form method="post" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Usunąć notatkę?');" class="d-inline">
            @csrf @method('delete')
            <button class="btn btn-outline-danger btn-sm">Usuń</button>
          </form>
          @endcan
        </div>
      </div>
    </div>
  </div>

  {{-- Treść --}}
  <div class="card mb-4">
    <div class="card-body d-flex gap-3">
      <div class="text-center" style="width:88px">
        <form method="post" action="{{ route('notes.vote', $note) }}" class="js-note-vote">
          @csrf
          <button class="btn btn-sm btn-outline-success d-block w-100" name="value" value="1" type="submit"><i class="bi bi-hand-thumbs-up"></i></button>
          <div class="small my-1">
            <span class="text-success" data-up>{{ $note->up ?? $note->thumbsUpCount() }}</span> /
            <span class="text-danger" data-down>{{ $note->down ?? $note->thumbsDownCount() }}</span>
          </div>
          <button class="btn btn-sm btn-outline-danger d-block w-100" name="value" value="-1" type="submit"><i class="bi bi-hand-thumbs-down"></i></button>
        </form>
      </div>
      <div class="flex-grow-1">
        @if($note->body)
          <div class="mb-3">{!! $note->body !!}</div>
        @endif

        @if($note->attachments && $note->attachments->count())
          <div class="small text-body-secondary mb-2">Załączniki:</div>
          <div class="d-flex flex-wrap gap-2">
            @foreach($note->attachments as $att)
              @php
                $nameLower = Str::lower($att->original_name);
                $isImage = Str::startsWith($att->mime_type, 'image/') || Str::endsWith($nameLower, ['.jpg','.jpeg','.png','.gif','.webp']);
              @endphp
              @if($isImage)
                <a href="{{ $att->publicUrl() }}" target="_blank"><img src="{{ $att->publicUrl() }}" class="img-thumbnail" style="max-height:120px" alt="{{ $att->original_name }}" title="{{ $att->original_name }}"></a>
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
  </div>

  {{-- Edycja (tylko dla autora <1h lub moder/admin) --}}
  @can('update', $note)
  @php $noteEditOpen = old('title') !== null; @endphp
  <button class="btn btn-outline-primary w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#noteEditForm" aria-expanded="{{ $noteEditOpen ? 'true' : 'false' }}" aria-controls="noteEditForm">
    Edytuj
  </button>
  <div id="noteEditForm" class="collapse {{ $noteEditOpen ? 'show' : '' }}">
    <div class="card mb-4">
      <div class="card-body">
        <h2 class="h5 fw-bold mb-3">Formularz edycji</h2>
        <form method="post" action="{{ route('notes.update', $note) }}" enctype="multipart/form-data" class="vstack gap-3">
          @csrf @method('put')
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Tytul</label>
              <input name="title" class="form-control" value="{{ old('title', $note->title) }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Data zajec</label>
              <input name="lecture_date" type="date" class="form-control" value="{{ optional($note->lecture_date)->format('Y-m-d') }}">
            </div>
          </div>
          <div>
            <label class="form-label">Dot. przedmiotu</label>
            <div class="form-control bg-body-secondary">{{ $note->subject->name ?? '---' }}</div>
          </div>
          <div>
            <label class="form-label">Tresc</label>
            <div class="d-flex gap-2 mb-2">
              <button class="btn btn-sm btn-outline-secondary js-bold" type="button"><i class="bi bi-type-bold"></i></button>
              <button class="btn btn-sm btn-outline-secondary js-italic" type="button"><i class="bi bi-type-italic"></i></button>
              <button class="btn btn-sm btn-outline-secondary js-ul" type="button"><i class="bi bi-list-ul"></i></button>
              <button class="btn btn-sm btn-outline-secondary js-link" type="button"><i class="bi bi-link-45deg"></i></button>
            </div>
            <div id="editor" contenteditable="true" class="form-control" style="min-height:140px">{!! old('body', $note->body) !!}</div>
            <input type="hidden" name="body" id="editorHidden" value="{{ old('body', $note->body) }}">
          </div>
          <div>
            <label class="form-label">Nowe zalaczniki</label>
            <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
          </div>
          @if($note->attachments->count())
          <div>
            <label class="form-label">Usun wybrane zalaczniki</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach($note->attachments as $att)
                <label class="form-check-label border rounded p-2">
                  <input type="checkbox" class="form-check-input me-2" name="remove_attachments[]" value="{{ $att->id }}">
                  {{ $att->original_name }}
                </label>
              @endforeach
            </div>
          </div>
          @endif
          <div class="text-end">
            <button class="btn btn-primary btn-sm">Zapisz</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endcan

  {{-- Komentarze --}}
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6">Komentarze</h2>
      @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
      @endif
      <div class="vstack gap-3 mt-2">
        @forelse($comments as $c)
          <div class="border rounded p-2">
            <div class="small text-body-secondary d-flex justify-content-between">
              <div>
                {{ $c->user->name ?? '—' }} • {{ $c->created_at->diffForHumans() }}
              </div>
              <div class="d-flex gap-2">
                <a href="#" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#reply-{{$c->id}}">Odpowiedz</a>
                @php $canOwner = auth()->id() === $c->user_id && $c->created_at->addHour()->isFuture(); @endphp
                @if($canOwner || (auth()->user() && in_array(auth()->user()->role, ['admin','moderator'], true)))
                  <form method="post" action="{{ route('note-comments.destroy', $c) }}" onsubmit="return confirm('Usunąć komentarz?')">
                    @csrf @method('delete')
                    <button class="btn btn-outline-danger btn-sm">Usuń</button>
                  </form>
                @endif
              </div>
            </div>
            <div class="mt-1">{{ $c->body }}</div>

            {{-- odpowiedzi --}}
            @if($c->replies->count())
              <div class="mt-2 ms-4 vstack gap-2">
                @foreach($c->replies as $r)
                  <div class="border rounded p-2">
                    <div class="small text-body-secondary d-flex justify-content-between">
                      <div>
                        {{ $r->user->name ?? '—' }} • {{ $r->created_at->diffForHumans() }}
                      </div>
                      @php $canOwnerR = auth()->id() === $r->user_id && $r->created_at->addHour()->isFuture(); @endphp
                      @if($canOwnerR || (auth()->user() && in_array(auth()->user()->role, ['admin','moderator'], true)))
                        <form method="post" action="{{ route('note-comments.destroy', $r) }}" onsubmit="return confirm('Usunąć komentarz?')">
                          @csrf @method('delete')
                          <button class="btn btn-outline-danger btn-sm">Usuń</button>
                        </form>
                      @endif
                    </div>
                    <div class="mt-1">{{ $r->body }}</div>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- formularz odpowiedzi --}}
            <div id="reply-{{$c->id}}" class="collapse mt-2 ms-4">
              <form method="post" action="{{ route('note-comments.store', $note) }}" class="vstack gap-2">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $c->id }}">
                <textarea name="body" rows="2" class="form-control" placeholder="Napisz odpowiedź..." minlength="2" required></textarea>
                <div class="text-end">
                  <button class="btn btn-primary btn-sm">Dodaj odpowiedź</button>
                </div>
              </form>
            </div>
          </div>
        @empty
          <div class="text-body-secondary">Brak komentarzy.</div>
        @endforelse
      </div>

      <form method="post" action="{{ route('note-comments.store', $note) }}" class="mt-3">
        @csrf
        <label class="form-label">Dodaj komentarz</label>
        <textarea name="body" rows="3" class="form-control" placeholder="Napisz komentarz..." minlength="2" required aria-describedby="commentHelp">{{ old('body') }}</textarea>
        <div id="commentHelp" class="form-text">Minimum 2 znaki.</div>
        @if($errors->has('body'))
          <div class="text-danger small mt-1">{{ $errors->first('body') }}</div>
        @endif
        <div class="text-end mt-2">
          <button class="btn btn-primary btn-sm">Dodaj</button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  // toolbar edytora
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

  // głosowanie AJAX
  document.querySelectorAll('.js-note-vote').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); if (form.dataset.loading==='1') return; form.dataset.loading='1';
      const btn = e.submitter; if (btn) btn.disabled=true;
      try {
        const fd = new FormData(form);
        if (btn && btn.name === 'value') fd.append('value', btn.value);
        const res = await fetch(form.action, { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':'{{ csrf_token() }}' }, body: fd });
        if (!res.ok) { if (res.status===401) { window.location='{{ route('login') }}'; return; } console.error(await res.text()); return; }
        const data = await res.json();
        const upEl = form.querySelector('[data-up]'); const downEl = form.querySelector('[data-down]');
        if (upEl) upEl.textContent = data.up ?? 0; if (downEl) downEl.textContent = data.down ?? 0;
      } catch(err){ console.error(err); } finally { if (btn) btn.disabled=false; form.dataset.loading='0'; }
    });
  });
</script>
@endpush
