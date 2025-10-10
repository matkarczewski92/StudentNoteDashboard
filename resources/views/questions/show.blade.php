@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">

  {{-- KARTA: Pytanie --}}
  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between gap-3">
      <div class="flex-grow-1">
        <h1 class="h4 fw-bold mb-2">{{ $question->title }}</h1>

        {{-- meta --}}
        <div class="small text-body-secondary">
          Autor: {{ $question->user->name ?? '—' }} · {{ $question->created_at->diffForHumans() }}
        </div>
      </div>

      <div class="text-end">
        @if($question->is_closed)
          <span class="badge text-bg-danger">Zamknięte</span>
        @else
          <span class="badge text-bg-success">Otwarte</span>
        @endif

        {{-- Akcje: Zamknij/Otwórz i Usuń --}}
        <div class="mt-2 d-flex justify-content-end gap-2">
          @can('update', $question)
            {{-- jeśli masz osobny endpoint toggle --}}
            @if(Route::has('questions.toggle'))
              <form method="post" action="{{ route('questions.toggle', $question) }}" class="d-inline">
                @csrf @method('patch')
                <button class="btn btn-outline-secondary btn-sm">
                  {{ $question->is_closed ? 'Otwórz' : 'Zamknij' }}
                </button>
              </form>
            @else
              {{-- fallback przez update --}}
              <form method="post" action="{{ route('questions.update', $question) }}" class="d-inline">
                @csrf @method('put')
                <input type="hidden" name="is_closed" value="{{ $question->is_closed ? 0 : 1 }}">
                <button class="btn btn-outline-secondary btn-sm">
                  {{ $question->is_closed ? 'Otwórz' : 'Zamknij' }}
                </button>
              </form>
            @endif
          @endcan

          @can('delete', $question)
            <form method="post" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Usunąć pytanie?');" class="d-inline">
              @csrf @method('delete')
              <button class="btn btn-outline-danger btn-sm">Usuń</button>
            </form>
          @endcan
        </div>
      </div>
    </div>
  </div>

  {{-- KARTA: Edycja pytania (pełna szerokość, jedno pole) --}}
  @can('update', $question)
    @if(auth()->id() === $question->user_id && $question->created_at->addMinutes(30)->isFuture())
      <div class="card mb-4">
        <div class="card-body">
          <form method="post" action="{{ route('questions.update', $question) }}">
            @csrf @method('put')
            <label class="form-label small text-body-secondary">Edytuj pytanie (dostępne 30 min od dodania)</label>
            <textarea name="title" rows="3" class="form-control" placeholder="Treść pytania" required>{{ old('title', $question->title) }}</textarea>
            <div class="text-end mt-2">
              <button class="btn btn-primary btn-sm">Zapisz</button>
              <a href="{{ route('questions.show', $question) }}" class="btn btn-outline-secondary btn-sm">Anuluj</a>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endcan

  {{-- KARTA: Dodaj odpowiedź (pomiędzy pytaniem a odpowiedziami) --}}
  @if(!$question->is_closed)
    <div id="reply" class="card mb-4">
      <div class="card-body">
        <form id="answer-form" method="post" action="{{ route('answers.store', $question) }}" enctype="multipart/form-data">
          @csrf
          <label class="form-label">Twoja odpowiedź</label>
          <textarea id="answer-body" name="body" rows="6" class="form-control" placeholder="Napisz odpowiedź…" required>{{ old('body') }}</textarea>

          <div class="mt-3">
            <label class="form-label small text-body-secondary">Załącz obrazki (możesz też wkleić zrzut ekranu: Ctrl+V)</label>
            <input id="answer-images" type="file" name="images[]" class="form-control" accept="image/*" multiple>
            <div id="answer-previews" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>

          <div class="text-end mt-3">
            <button class="btn btn-success">Dodaj odpowiedź</button>
          </div>
        </form>
      </div>
    </div>
  @else
    <div class="alert alert-secondary">Pytanie jest zamknięte — dodawanie odpowiedzi wyłączone.</div>
  @endif

  {{-- LISTA ODPOWIEDZI (najnowsze na górze) --}}
  @foreach($answers as $a)
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between gap-3">
          <div class="flex-grow-1">
            <div class="text-wrap">{{ $a->body }}</div>
            <div class="small text-body-secondary mt-2">
              {{ $a->user->name ?? '—' }} · {{ $a->created_at->diffForHumans() }}
            </div>

            {{-- załączone obrazki --}}
            @if($a->attachments->count())
              <div class="mt-3 d-flex flex-wrap gap-2">
                @foreach($a->attachments as $att)
                  <a href="{{ asset('storage/'.$att->path) }}" target="_blank" class="d-inline-block">
                    <img src="{{ asset('storage/'.$att->path) }}" alt="załącznik" class="img-thumbnail" style="max-height:120px">
                  </a>
                @endforeach
              </div>
            @endif
          </div>

          {{-- panel głosów --}}
          <div class="text-center" style="width: 5rem;">
            <form method="post" action="{{ route('answers.vote', $a) }}" class="vote" data-answer-id="{{ $a->id }}">
              @csrf
              <input type="hidden" name="value" value="1">
              <button type="submit" class="btn btn-outline-secondary btn-sm w-100 mb-1" aria-label="Kciuk w górę">👍</button>
              <div class="small" data-up>{{ $a->thumbsUpCount() }}</div>
            </form>
            <form method="post" action="{{ route('answers.vote', $a) }}" class="vote mt-2" data-answer-id="{{ $a->id }}">
              @csrf
              <input type="hidden" name="value" value="-1">
              <button type="submit" class="btn btn-outline-secondary btn-sm w-100" aria-label="Kciuk w dół">👎</button>
              <div class="small" data-down>{{ $a->thumbsDownCount() }}</div>
            </form>
          </div>
        </div>

        {{-- Akcje: edycja (pełna szerokość, collapse) / usuwanie --}}
        <div class="mt-3 d-flex flex-wrap gap-2">
          @can('update', $a)
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#edit-ans-{{ $a->id }}">Edytuj</button>
          @endcan
          @can('delete', $a)
            <form method="post" action="{{ route('answers.destroy', $a) }}" onsubmit="return confirm('Usunąć odpowiedź?');">
              @csrf @method('delete')
              <button class="btn btn-outline-danger btn-sm">Usuń</button>
            </form>
          @endcan
        </div>

        @can('update', $a)
          <div id="edit-ans-{{ $a->id }}" class="collapse mt-3">
            <div class="card">
              <div class="card-body">
                <form method="post" action="{{ route('answers.update', $a) }}" enctype="multipart/form-data">
                  @csrf @method('put')

                  <textarea name="body" rows="6" class="form-control" required>{{ $a->body }}</textarea>

                  {{-- istniejące obrazki do ewentualnego usunięcia --}}
                  @if($a->attachments->count())
                    <div class="mt-3">
                      <div class="form-label small text-body-secondary">Usuń wybrane załączniki:</div>
                      <div class="d-flex flex-wrap gap-3">
                        @foreach($a->attachments as $att)
                          <label class="form-check-label">
                            <input class="form-check-input me-1" type="checkbox" name="remove_attachments[]" value="{{ $att->id }}">
                            <img src="{{ asset('storage/'.$att->path) }}" class="img-thumbnail" style="max-height:100px">
                          </label>
                        @endforeach
                      </div>
                    </div>
                  @endif

                  {{-- nowe obrazki --}}
                  <div class="mt-3">
                    <label class="form-label small text-body-secondary">Dodaj obrazki</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                  </div>

                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-sm">Zapisz</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endcan
      </div>
    </div>
  @endforeach

  {{-- paginacja odpowiedzi --}}
  <div class="mt-4">
    {{ $answers->links() }}
  </div>

</div>

{{-- SKRYPTY: głosowanie + wklejanie/drag&drop obrazków do formularza odpowiedzi --}}
@push('scripts')
<script>
  // --- GŁOSOWANIE (AJAX z JSON) ---
  document.querySelectorAll('form.vote').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (form.dataset.loading === '1') return;
      form.dataset.loading = '1';
      const btn = form.querySelector('button'); if (btn) btn.disabled = true;

      try {
        const fd = new FormData(form);
        const res = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: fd
        });
        if (!res.ok) {
          if (res.status === 401) { window.location = '{{ route('login') }}'; return; }
          console.error('Vote failed', res.status, await res.text()); return;
        }
        const data = await res.json();

        const panel = form.closest('.text-center') ?? form.closest('[style*="width"]');
        if (panel) {
          const upEl = panel.querySelector('[data-up]');
          const downEl = panel.querySelector('[data-down]');
          if (upEl)   upEl.textContent = data.up ?? 0;
          if (downEl) downEl.textContent = data.down ?? 0;
          panel.classList.add('opacity-75'); setTimeout(()=>panel.classList.remove('opacity-75'), 120);
        }
      } catch (err) {
        console.error('Vote error', err);
      } finally {
        if (btn) btn.disabled = false;
        form.dataset.loading = '0';
      }
    });
  });

  // --- WKLEJANIE I PREVIEW OBRAZKÓW W FORMULARZU ODPOWIEDZI ---
  (function(){
    const ta = document.getElementById('answer-body');
    const fileInput = document.getElementById('answer-images');
    const previews = document.getElementById('answer-previews');
    if (!ta || !fileInput || !previews) return;

    function addFiles(files) {
      const dt = new DataTransfer();
      for (const f of fileInput.files) dt.items.add(f);
      for (const f of files) {
        if (!f.type.startsWith('image/')) continue;
        dt.items.add(f);
        const img = document.createElement('img');
        img.className = 'img-thumbnail';
        img.style.maxHeight = '100px';
        img.alt = 'podgląd';
        img.src = URL.createObjectURL(f);
        previews.appendChild(img);
      }
      fileInput.files = dt.files;
    }

    ta.addEventListener('paste', (e) => {
      const items = e.clipboardData?.items || [];
      const images = [];
      for (const item of items) {
        if (item.kind === 'file') {
          const file = item.getAsFile();
          if (file && file.type.startsWith('image/')) images.push(file);
        }
      }
      if (images.length) { e.preventDefault(); addFiles(images); }
    });

    ['dragover','dragenter'].forEach(ev => ta.addEventListener(ev, e => { e.preventDefault(); ta.classList.add('border','border-primary'); }));
    ['dragleave','drop'].forEach(ev => ta.addEventListener(ev, e => { e.preventDefault(); ta.classList.remove('border','border-primary'); }));
    ta.addEventListener('drop', (e) => {
      const files = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
      if (files.length) addFiles(files);
    });
  })();
</script>
@endpush
@endsection
