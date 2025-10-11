@php
  $poll->loadMissing(['options' => fn($q) => $q->withCount('votes')]);
  $userVoteIds = \App\Models\PollVote::where('poll_id', $poll->id)->where('user_id', auth()->id())->pluck('poll_option_id')->all();
@endphp

<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="fw-semibold">{{ $poll->title }}</div>
      <a href="{{ route('polls.show', $poll) }}" class="btn btn-outline-secondary btn-sm">Szczegóły</a>
    </div>

    <form class="vstack gap-2" data-poll-widget data-id="{{ $poll->id }}" data-stats="{{ route('polls.stats',$poll) }}" data-vote="{{ route('polls.vote',$poll) }}" data-multiple="{{ $poll->is_multiple ? '1' : '0' }}">
      @csrf
      @foreach($poll->options as $opt)
        <div>
          <label class="d-flex align-items-center gap-2">
            <input type="{{ $poll->is_multiple ? 'checkbox' : 'radio' }}" name="{{ $poll->is_multiple ? 'option_ids[]' : 'option_id' }}" value="{{ $opt->id }}" @checked(in_array($opt->id,$userVoteIds)) {{ $poll->is_closed ? 'disabled' : '' }}>
            <span class="flex-grow-1">{{ $opt->label }}</span>
            <span class="small text-body-secondary" data-votes="{{ $opt->id }}">{{ $opt->votes_count }}</span>
          </label>
          <div class="progress"><div class="progress-bar" data-bar="{{ $opt->id }}" style="width:0%"></div></div>
        </div>
      @endforeach
      <div class="d-flex align-items-center justify-content-between mt-2">
        <div class="small text-body-secondary">Głosów: <span data-total>0</span></div>
        @if(!$poll->is_closed)
          <button type="button" class="btn btn-success btn-sm" data-vote-btn>Głosuj</button>
        @endif
      </div>
    </form>
  </div>
  </div>

@push('scripts')
<script>
  (function(){
    document.querySelectorAll('[data-poll-widget]').forEach((wrap) => {
      const STATS = wrap.dataset.stats;
      const VOTE = wrap.dataset.vote;
      const MULTIPLE = wrap.dataset.multiple === '1';
      const totalEl = wrap.querySelector('[data-total]');
      async function refresh(){
        try {
          const res = await fetch(STATS);
          if (!res.ok) return;
          const data = await res.json();
          if (totalEl) totalEl.textContent = data.total ?? 0;
          for (const o of data.options || []) {
            wrap.querySelector(`[data-bar="${o.id}"]`)?.style.setProperty('width', (o.percent||0)+'%');
            const vc = wrap.querySelector(`[data-votes="${o.id}"]`); if (vc) vc.textContent = o.votes ?? 0;
          }
        } catch {}
      }
      refresh(); setInterval(refresh, 3000);

      wrap.querySelector('[data-vote-btn]')?.addEventListener('click', async () => {
        const fd = new FormData();
        const token = wrap.querySelector('input[name=_token]')?.value; if (token) fd.append('_token', token);
        if (MULTIPLE) {
          wrap.querySelectorAll('input[name="option_ids[]"]:checked').forEach(i => fd.append('option_ids[]', i.value));
        } else { const s = wrap.querySelector('input[name="option_id"]:checked'); if (s) fd.append('option_id', s.value); }
        const res = await fetch(VOTE, { method: 'POST', body: fd });
        await refresh();
      });
    });
  })();
</script>
@endpush

