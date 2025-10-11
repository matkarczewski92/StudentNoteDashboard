<div class="card">
  <div class="card-body d-flex gap-3">
    <div class="text-center" style="width:72px">
      <form method="post" action="{{ route('notes.vote', $note) }}" class="js-note-vote" data-note-id="{{ $note->id }}">
        @csrf
        <button class="btn btn-sm btn-outline-success d-block w-100" name="value" value="1" type="submit"><i class="bi bi-hand-thumbs-up"></i></button>
        <div class="small my-1">
          <span class="text-success" data-up>{{ $note->thumbsUpCount() }}</span> / <span class="text-danger" data-down>{{ $note->thumbsDownCount() }}</span>
        </div>
        <button class="btn btn-sm btn-outline-danger d-block w-100" name="value" value="-1" type="submit"><i class="bi bi-hand-thumbs-down"></i></button>
      </form>
    </div>

    <div class="flex-grow-1">
      <div class="d-flex align-items-center justify-content-between">
        <div class="small text-body-secondary">
          @if($note->lecture_date)
            <span class="badge text-bg-secondary">{{ $note->lecture_date->format('Y-m-d') }}</span>
          @endif
          <span class="badge {{ $note->kindBadgeClass() }} ms-1">{{ $note->kindLabel() }}</span>
          <span class="ms-2">Dodano: {{ $note->created_at->diffForHumans() }}</span>
          <span class="ms-2">Autor: {{ $note->user->name ?? '—' }}</span>
          @php $groups = optional($note->user)->groups?->pluck('name')->filter()->values() ?? collect(); @endphp
          @if($groups->count())
            <span class="ms-2">Grupa: {{ $groups->join(', ') }}</span>
          @endif
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('notes.show', $note) }}" class="btn btn-outline-secondary btn-sm">Otwórz</a>
          @can('delete', $note)
            <form method="post" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Usunąć notatkę?');">
              @csrf @method('delete')
              <button class="btn btn-outline-danger btn-sm">Usuń</button>
            </form>
          @endcan
        </div>
      </div>

      <div class="mt-2">
        <a href="{{ route('notes.show', $note) }}" class="link-body-emphasis text-decoration-none">
          <div class="fs-6">{{ $note->title }}</div>
        </a>
      </div>
    </div>
  </div>
</div>
