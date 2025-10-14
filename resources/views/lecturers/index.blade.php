@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h2 fw-bold mb-3">Od wykładowców</h1>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Semestr</label>
          <select id="semesterSelect" class="form-select">
            @foreach($semesters as $s)
              <option value="{{ $s->id }}" {{ (int)$selectedSemesterId === (int)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label">Przedmiot</label>
          <select id="subjectSelect" class="form-select">
            <option value="">— Wybierz przedmiot —</option>
            @foreach($subjects as $sub)
              <option value="{{ $sub->id }}" {{ (int)$selectedSubjectId === (int)$sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  @if($selectedSubjectId)
    <div class="mb-3 text-end">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMailModal">
        <i class="bi bi-plus"></i> Dodaj wpis
      </button>
    </div>

    <div class="modal fade" id="addMailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nowy wpis</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
          </div>
          <div class="modal-body">
            <form id="mail-create" method="post" action="{{ route('lecturers.store') }}" enctype="multipart/form-data" class="vstack gap-3">
              @csrf
              <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Tytuł</label>
                  <input name="title" class="form-control" placeholder="Krótki tytuł" required>
                </div>
              </div>
              @php $selSubject = $subjects->firstWhere('id', $selectedSubjectId); @endphp
              <div>
                <label class="form-label">Dot. przedmiotu</label>
                <div class="form-control bg-body-secondary">{{ $selSubject->name ?? '—' }}</div>
              </div>
              <div>
                <label class="form-label">Treść (opcjonalnie)</label>
                <div class="d-flex gap-2 mb-2">
                  <button class="btn btn-sm btn-outline-secondary js-bold" type="button"><i class="bi bi-type-bold"></i></button>
                  <button class="btn btn-sm btn-outline-secondary js-italic" type="button"><i class="bi bi-type-italic"></i></button>
                  <button class="btn btn-sm btn-outline-secondary js-ul" type="button"><i class="bi bi-list-ul"></i></button>
                  <button class="btn btn-sm btn-outline-secondary js-link" type="button"><i class="bi bi-link-45deg"></i></button>
                </div>
                <div id="editor" contenteditable="true" class="form-control" style="min-height:140px"></div>
                <input type="hidden" name="body" id="editorHidden">
              </div>
              <div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="mail-only-groups">
                  <label class="form-check-label" for="mail-only-groups">Czy dotyczy tylko jednej z grup </label>
                </div>
                <div id="mail-groups-wrap" class="d-none">
                  <label class="form-label d-block">Dotyczy grup:</label>
                  <div class="row row-cols-1 row-cols-sm-2 g-2">
                    @php $groupsList = (auth()->user() && auth()->user()->can('moderate')) ? $allGroups : $userGroups; @endphp
                    @foreach($groupsList as $g)
                      <div class="col">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="group_ids[]" value="{{ $g->id }}" id="mail-g-{{ $g->id }}" disabled>
                          <label class="form-check-label" for="mail-g-{{ $g->id }}">{{ $g->name }}</label>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
              <div>
                <label class="form-label">Załączniki</label>
                <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button>
            <button type="submit" class="btn btn-primary" form="mail-create">Dodaj wpis</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  @if($selectedSubjectId && $mails && $mails->count())
    <div class="vstack gap-3">
      @foreach($mails as $mail)
        @include('lecturers._card', ['mail' => $mail])
      @endforeach
    </div>
    <div class="mt-3">{{ $mails->appends(['semester_id'=>$selectedSemesterId,'subject_id'=>$selectedSubjectId])->links() }}</div>
  @else
    <div class="text-body-secondary">Brak wpisów do wyświetlenia.</div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  const semSel = document.getElementById('semesterSelect');
  const subSel = document.getElementById('subjectSelect');
  if (semSel) semSel.addEventListener('change', async (e) => {
    const sid = e.target.value;
    try {
      const res = await fetch(`{{ url('/ajax/semesters') }}/${sid}/subjects`);
      const data = await res.json();
      subSel.innerHTML = '';
      const ph = document.createElement('option'); ph.value=''; ph.textContent='— Wybierz przedmiot —'; subSel.appendChild(ph);
      data.forEach(s => { const opt = document.createElement('option'); opt.value=s.id; opt.textContent=s.name; subSel.appendChild(opt); });
      window.location = `{{ route('lecturers.index') }}?semester_id=${sid}`;
    } catch(err){ console.error(err); }
  });
  if (subSel) subSel.addEventListener('change', (e) => {
    const sid = semSel.value, sub = e.target.value;
    if (sub) window.location = `{{ route('lecturers.index') }}?semester_id=${sid}&subject_id=${sub}`;
    else window.location = `{{ route('lecturers.index') }}?semester_id=${sid}`;
  });

  (function(){
    const ed = document.getElementById('editor');
    const hidden = document.getElementById('editorHidden');
    const form = document.getElementById('mail-create');
    if (!ed || !form) return;
    function cmd(c, v=null){ document.execCommand(c, false, v); ed.focus(); }
    document.querySelector('.js-bold')?.addEventListener('click', ()=>cmd('bold'));
    document.querySelector('.js-italic')?.addEventListener('click', ()=>cmd('italic'));
    document.querySelector('.js-ul')?.addEventListener('click', ()=>cmd('insertUnorderedList'));
    document.querySelector('.js-link')?.addEventListener('click', ()=>{ const u = prompt('Adres URL'); if(u) cmd('createLink', u); });
    form.addEventListener('submit', ()=>{ hidden.value = ed.innerHTML.trim(); });
  })();

  (function(){
    const toggle = document.getElementById('mail-only-groups');
    const wrap = document.getElementById('mail-groups-wrap');
    const inputs = wrap ? wrap.querySelectorAll('input[type="checkbox"][name="group_ids[]"]') : [];
    if (toggle && wrap) {
      toggle.addEventListener('change', () => {
        wrap.classList.toggle('d-none', !toggle.checked);
        inputs.forEach(i => i.disabled = !toggle.checked);
      });
    }
  })();
</script>
@endpush
