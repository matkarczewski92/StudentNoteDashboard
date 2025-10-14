<div class="card">
  <div class="card-body d-flex gap-3">
    <div class="flex-grow-1">
      <div class="d-flex align-items-center justify-content-between">
        <div class="small text-body-secondary">
          <span>Dodano: {{ $mail->created_at->diffForHumans() }}</span>
          <span class="ms-2">Autor: {{ $mail->user->name ?? '—' }}</span>
          @php $groups = optional($mail->user)->groups?->pluck('name')->filter()->values() ?? collect(); @endphp
          @if($groups->count())
            <span class="ms-2">Grupa: {{ $groups->join(', ') }}</span>
          @endif
          <span class="ms-2">Przedmiot: {{ $mail->subject->name ?? '—' }}</span>
          @if($mail->groups && $mail->groups->count())
            <span class="badge text-bg-warning text-dark ms-2">Grupy: {{ $mail->groups->pluck('name')->join(', ') }}</span>
          @endif
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('lecturers.show', $mail) }}" class="btn btn-outline-secondary btn-sm">Otwórz</a>
          @can('delete', $mail)
            <form method="post" action="{{ route('lecturers.destroy', $mail) }}" onsubmit="return confirm('Usunąć wpis?');">
              @csrf @method('delete')
              <button class="btn btn-outline-danger btn-sm">Usuń</button>
            </form>
          @endcan
        </div>
      </div>

      <div class="mt-2">
        <a href="{{ route('lecturers.show', $mail) }}" class="link-body-emphasis text-decoration-none">
          <div class="fs-6">{{ $mail->title }}</div>
        </a>
      </div>
    </div>
  </div>
</div>
