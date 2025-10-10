@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">

  {{-- KARTA: Pytanie --}}
  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between gap-3">
      <div class="flex-grow-1">
        <h1 class="h4 fw-bold mb-2">{{ $question->title }}</h1>
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

        <div class="mt-2 d-flex justify-content-end gap-2">
          @can('update', $question)
            @if(Route::has('questions.toggle'))
              <form method="post" action="{{ route('questions.toggle', $question) }}" class="d-inline">
                @csrf @method('patch')
                <button class="btn btn-outline-secondary btn-sm">
                  {{ $question->is_closed ? 'Otwórz' : 'Zamknij' }}
                </button>
              </form>
            @else
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

  {{-- KARTA: Ankieta powiązana z pytaniem --}}
  @if($question->poll)
    @include('polls._widget', ['poll' => $question->poll])
  @else
    @can('update', $question)
      <div class="card mb-4">
        <div class="card-body">
          <form method="post" action="{{ route('polls.store') }}" class="vstack gap-2">
            @csrf
            <input type="hidden" name="question_id" value="{{ $question->id }}">
            <label class="form-label">Dodaj ankietę do tego pytania</label>
            <input name="title" class="form-control" placeholder="Tytuł ankiety" required>
            <div class="vstack gap-2">
              <input name="options[]" class="form-control" placeholder="Opcja 1" required>
              <input name="options[]" class="form-control" placeholder="Opcja 2" required>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="q_is_multiple" name="is_multiple">
                <label class="form-check-label" for="q_is_multiple">Wielokrotny wybór</label>
              </div>
              <button class="btn btn-outline-primary btn-sm">Utwórz ankietę</button>
            </div>
          </form>
        </div>
      </div>
    @endcan
  @endif

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

  {{-- KARTA: Dodaj odpowiedź --}}
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

  {{-- LISTA ODPOWIEDZI --}}
  @foreach($answers as $a)
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between gap-3">
          <div class="flex-grow-1">
            <div class="text-wrap">{{ $a->body }}</div>
            <div class="small text-body-secondary mt-2">
              {{ $a->user->name ?? '—' }} · {{ $a->created_at->diffForHumans() }}
            </div>

            {{-- miniatury (klik = modal) --}}
            @if($a->attachments->count())
              <div class="mt-3 d-flex flex-wrap gap-2">
                @foreach($a->attachments as $idx => $att)
                  <img
                    src="{{ asset('storage/'.$att->path) }}"
                    alt="załącznik"
                    class="img-thumbnail"
                    style="max-height:120px; cursor: zoom-in"
                    data-group="ans-{{ $a->id }}"
                    data-index="{{ $idx }}"
                    data-full="{{ asset('storage/'.$att->path) }}"
                  >
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

        {{-- Akcje: edycja pełna szerokość / usuwanie --}}
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

{{-- MODAL: podgląd obrazka --}}
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-dark text-white">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute end-0 m-3" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        <img id="imgModalImg" src="" alt="podgląd" class="img-fluid w-100">
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-light btn-sm" id="imgPrev">‹ Poprzedni</button>
        <div class="small text-white-50" id="imgCaption"></div>
        <button type="button" class="btn btn-outline-light btn-sm" id="imgNext">Następny ›</button>
      </div>
    </div>
  </div>
</div>

{{-- SKRYPTY: głosowanie + wklejanie/drag&drop obrazków + modal galerii --}}
@push('scripts')
<script>
  // --- GŁOSOWANIE (AJAX) ---
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

  // --- WKLEJANIE / DRAG&DROP obrazków do formularza odpowiedzi ---
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

  // --- MODAL GALERII obrazków ---
  (function(){
    const modalEl = document.getElementById('imgModal');
    if (!modalEl) return;

    const modal = new window.bootstrap.Modal(modalEl);
    const imgEl = document.getElementById('imgModalImg');
    const captionEl = document.getElementById('imgCaption');
    const prevBtn = document.getElementById('imgPrev');
    const nextBtn = document.getElementById('imgNext');

    let groups = {};          // { groupId: [url, url, ...] }
    let currentGroup = null;  // aktywna grupa
    let currentIndex = 0;     // indeks aktywnego obrazka

    function buildGroups(){
      groups = {};
      document.querySelectorAll('img[data-group]').forEach(img => {
        const g = img.dataset.group;
        const url = img.dataset.full || img.src;
        if (!groups[g]) groups[g] = [];
        if (!groups[g].includes(url)) groups[g].push(url);

        img.addEventListener('click', () => {
          currentGroup = g;
          const arr = groups[g] || [];
          const idxAttr = Number(img.dataset.index);
          currentIndex = Number.isFinite(idxAttr) ? idxAttr : Math.max(0, arr.indexOf(url));
          open(arr[currentIndex], `${currentIndex+1} / ${arr.length}`);
        }, { passive: true });
      });
    }

    function open(url, caption='') {
      imgEl.src = url;
      captionEl.textContent = caption;
      modal.show();
      // fokus na modal, aby działały strzałki
      modalEl.focus();
    }
    function showIndex(i){
      const arr = groups[currentGroup] || [];
      if (!arr.length) return;
      currentIndex = (i + arr.length) % arr.length; // pętla
      open(arr[currentIndex], `${currentIndex+1} / ${arr.length}`);
    }

    prevBtn.addEventListener('click', () => showIndex(currentIndex - 1));
    nextBtn.addEventListener('click', () => showIndex(currentIndex + 1));
    modalEl.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') showIndex(currentIndex - 1);
      if (e.key === 'ArrowRight') showIndex(currentIndex + 1);
    });

    buildGroups();
  })();
</script>
@endpush
@endsection
