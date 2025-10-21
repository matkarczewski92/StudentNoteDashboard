@extends('layouts.app')

@section('content')
<div class="container px-3 px-md-4">
  <h1 class="h2 fw-bold mb-3">Notatki</h1>

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

  {{-- Filtry: Semestr, Przedmiot i opcjonalnie Grupy --}}
  @php
    $groupsList = $groups ?? collect();
    $selectedGroups = collect($selectedGroupIds ?? []);
    $userGroupIdsList = collect($userGroupIds ?? []);
    $showGroupFilter = ($selectedSubjectKind ?? null) === 'exercise' && $groupsList->count() > 0;
    $subjectColClass = $showGroupFilter ? 'col-md-4' : 'col-md-8';
    $paginationParams = [
      'semester_id' => $selectedSemesterId,
      'subject_id'  => $selectedSubjectId,
    ];
    if (!empty($selectedGroupIds)) {
      $paginationParams['group_ids'] = $selectedGroupIds;
    }
  @endphp
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
        <div class="{{ $subjectColClass }}">
          <label class="form-label">Przedmiot</label>
          <select id="subjectSelect" class="form-select">
            <option value="">— Wybierz przedmiot —</option>
            @foreach($subjects as $sub)
              <option value="{{ $sub->id }}" {{ (int)$selectedSubjectId === (int)$sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
            @endforeach
          </select>
        </div>
        @if($showGroupFilter)
          <div class="col-md-4">
            <label class="form-label d-block">Grupy</label>
            <div id="groupFilter" class="vstack gap-1" data-total="{{ $groupsList->count() }}">
              @foreach($groupsList as $group)
                @php
                  $groupId = (int) $group->id;
                  $isChecked = $selectedGroups->contains($groupId);
                  $isMine = $userGroupIdsList->contains($groupId);
                @endphp
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="group-filter-{{ $group->id }}" value="{{ $group->id }}" {{ $isChecked ? 'checked' : '' }}>
                  <label class="form-check-label" for="group-filter-{{ $group->id }}">
                    {{ $group->name }}
                    @if($isMine)
                      <span class="small text-body-secondary ms-1">(moja grupa)</span>
                    @endif
                  </label>
                </div>
              @endforeach
            </div>
            <div class="form-text">Pozostaw pola niezaznaczone, aby wyświetlić notatki ze wszystkich grup.</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Dodaj notatkę (przycisk -> modal) --}}
  @if($selectedSubjectId)
    <div class="mb-3 text-end">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
        <i class="bi bi-plus"></i> Dodaj notatkę
      </button>
    </div>

    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nowa notatka</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
          </div>
          <div class="modal-body">
            <form id="note-create" method="post" action="{{ route('notes.store') }}" enctype="multipart/form-data" class="vstack gap-3">
              @csrf
              <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Tytuł</label>
                  <input name="title" class="form-control" placeholder="Krótki tytuł notatki" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Data zajęć (opcjonalnie)</label>
                  <input name="lecture_date" type="date" class="form-control" value="{{ now()->format('Y-m-d') }}">
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
                <label class="form-label">Załączniki</label>
                <input type="file" name="attachments[]" id="note-files" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button>
            <button type="submit" class="btn btn-primary" form="note-create">Dodaj notatkę</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Lista notatek --}}
  @if($selectedSubjectId && $notes && $notes->count())
    @php $currentDateHeader = null; $printedNoDateHeader = false; @endphp
    <div class="vstack gap-3">
      @foreach($notes as $note)
        @php $d = $note->lecture_date ? $note->lecture_date->format('Y-m-d') : null; @endphp
        @if($d && $d !== $currentDateHeader)
          @php $currentDateHeader = $d; @endphp
          <div class="mt-2">
            <div class="small text-uppercase text-body-secondary">Data zajęć</div>
            <h6 class="mb-1">{{ $currentDateHeader }}</h6>
            <hr class="mt-1 mb-3">
          </div>
        @elseif(!$d && !$printedNoDateHeader)
          @php $printedNoDateHeader = true; @endphp
          <div class="mt-2">
            <div class="small text-uppercase text-body-secondary">Data zajęć</div>
            <h6 class="mb-1">Bez daty</h6>
            <hr class="mt-1 mb-3">
          </div>
        @endif
        @include('notes._card', ['note' => $note])
      @endforeach
    </div>
    <div class="mt-3">{{ $notes->appends($paginationParams)->links() }}</div>
  @else
    <div class="text-body-secondary">Brak notatek do wyświetlenia.</div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  // przełączanie selectów
  const notesIndexUrl = "{{ route('notes.index') }}";
  const semSel = document.getElementById('semesterSelect');
  const subSel = document.getElementById('subjectSelect');
  const groupFilter = document.getElementById('groupFilter');
  if (semSel) semSel.addEventListener('change', async (e) => {
    const sid = e.target.value;
    try {
      const res = await fetch(`{{ url('/ajax/semesters') }}/${sid}/subjects`);
      const data = await res.json();
      subSel.innerHTML = '';
      const ph = document.createElement('option'); ph.value = ''; ph.textContent = '— Wybierz przedmiot —'; subSel.appendChild(ph);
      data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id; opt.textContent = s.name; subSel.appendChild(opt);
      });
      const params = new URLSearchParams();
      if (sid) params.set('semester_id', sid);
      const qs = params.toString();
      window.location = `${notesIndexUrl}${qs ? `?${qs}` : ''}`;
    } catch(err) { console.error(err); }
  });
  if (subSel) subSel.addEventListener('change', (e) => {
    const sid = semSel.value, sub = e.target.value;
    const params = new URLSearchParams();
    if (sid) params.set('semester_id', sid);
    if (sub) params.set('subject_id', sub);
    const qs = params.toString();
    window.location = `${notesIndexUrl}${qs ? `?${qs}` : ''}`;
  });
  if (groupFilter) {
    const total = parseInt(groupFilter.dataset.total ?? '0', 10);
    const checkboxes = Array.from(groupFilter.querySelectorAll('input[type="checkbox"]'));
    const handleGroupChange = () => {
      const sid = semSel?.value ?? '';
      const sub = subSel?.value ?? '';
      const selectedValues = checkboxes.filter(cb => cb.checked).map(cb => cb.value);
      const params = new URLSearchParams();
      if (sid) params.set('semester_id', sid);
      if (sub) params.set('subject_id', sub);
      const treatAsAll = selectedValues.length === 0 || (total > 0 && selectedValues.length === total);
      if (!treatAsAll) {
        selectedValues.forEach(val => params.append('group_ids[]', val));
      }
      const qs = params.toString();
      window.location = `${notesIndexUrl}${qs ? `?${qs}` : ''}`;
    };
    checkboxes.forEach(cb => cb.addEventListener('change', handleGroupChange));
  }

  // prosty toolbar edytora
  (function(){
    const ed = document.getElementById('editor');
    const hidden = document.getElementById('editorHidden');
    const form = document.getElementById('note-create');
    if (!ed || !form) return;
    function cmd(c, v=null){ document.execCommand(c, false, v); ed.focus(); }
    document.querySelector('.js-bold')?.addEventListener('click', ()=>cmd('bold'));
    document.querySelector('.js-italic')?.addEventListener('click', ()=>cmd('italic'));
    document.querySelector('.js-ul')?.addEventListener('click', ()=>cmd('insertUnorderedList'));
    document.querySelector('.js-link')?.addEventListener('click', ()=>{ const u = prompt('Adres URL'); if(u) cmd('createLink', u); });
    form.addEventListener('submit', ()=>{ hidden.value = ed.innerHTML.trim(); });
  })();

  // głosowanie (przekazujemy kliknięty submitter -> value)
  document.querySelectorAll('.js-note-vote').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); if (form.dataset.loading === '1') return; form.dataset.loading = '1';
      const btn = e.submitter; if (btn) btn.disabled = true;
      try {
        const fd = new FormData(form);
        if (btn && btn.name === 'value') fd.append('value', btn.value);
        const res = await fetch(form.action, { method: 'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: fd });
        if (!res.ok) { if (res.status===401) { window.location='{{ route('login') }}'; return; } console.error(await res.text()); return; }
        const data = await res.json();
        const upEl = form.querySelector('[data-up]'); const downEl = form.querySelector('[data-down]');
        if (upEl) upEl.textContent = data.up ?? 0; if (downEl) downEl.textContent = data.down ?? 0;
      } catch(err) { console.error(err); } finally { if (btn) btn.disabled=false; form.dataset.loading='0'; }
    });
  });
</script>
@endpush
