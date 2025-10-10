@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- KARTA: Pytanie --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-snug">{{ $question->title }}</h1>

                @if($question->body)
                    <p class="mt-2 text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $question->body }}</p>
                @endif

                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Autor: {{ $question->user->name ?? '—' }} · {{ $question->created_at->diffForHumans() }}
                </div>
            </div>

            <div class="text-right space-y-2">
                @if($question->is_closed)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Zamknięte</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Otwarte</span>
                @endif

                <div class="space-x-2 block">
                    @can('update', $question)
                        <form method="post" action="{{ route('questions.update', $question) }}" class="inline">
                            @csrf @method('put')
                            <input type="hidden" name="is_closed" value="{{ $question->is_closed ? 0 : 1 }}">
                            <button class="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                                {{ $question->is_closed ? 'Otwórz' : 'Zamknij' }}
                            </button>
                        </form>
                    @endcan
                    @can('delete', $question)
                        <form method="post" action="{{ route('questions.destroy', $question) }}" class="inline" onsubmit="return confirm('Usunąć pytanie?');">
                            @csrf @method('delete')
                            <button class="rounded-lg border px-3 py-1 text-sm text-red-600 hover:bg-red-50 dark:border-white/10 dark:hover:bg-white/5">Usuń</button>
                        </form>
                    @endcan>
                </div>
            </div>
        </div>

        {{-- Inline edycja pytania (autor 30 min) --}}
        @can('update', $question)
            @if(auth()->id() === $question->user_id && $question->created_at->addMinutes(30)->isFuture())
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400">Edytuj pytanie (30 min)</summary>
                    <form method="post" action="{{ route('questions.update', $question) }}" class="mt-3 space-y-3">
                        @csrf @method('put')
                        <input name="title" class="w-full rounded-xl border px-4 py-3 dark:border-white/10 dark:bg-zinc-800" value="{{ old('title', $question->title) }}" required>
                        <textarea name="body" rows="4" class="w-full rounded-xl border px-4 py-3 dark:border-white/10 dark:bg-zinc-800">{{ old('body', $question->body) }}</textarea>
                        <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Zapisz</button>
                    </form>
                </details>
            @endif
        @endcan
    </div>

    {{-- KARTA: dodaj odpowiedź (pomiędzy pytaniem a odpowiedziami, full width) --}}
    @if(!$question->is_closed)
        <div class="rounded-2xl border border-gray-200/60 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <form method="post" action="{{ route('answers.store', $question) }}" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Twoja odpowiedź</label>
                <textarea name="body" rows="5"
                          class="w-full rounded-xl border border-gray-300/80 bg-white px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-white/10 dark:bg-zinc-800 dark:text-gray-100"
                          placeholder="Napisz odpowiedź…" required>{{ old('body') }}</textarea>
                <div class="flex justify-end">
                    <button class="inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-white hover:bg-emerald-700">
                        Dodaj odpowiedź
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-white/10 dark:text-gray-400">
            Pytanie jest zamknięte — dodawanie odpowiedzi wyłączone.
        </div>
    @endif

    {{-- LISTA odpowiedzi (karty jak w wątku) --}}
    <div id="answers" class="space-y-3">
        @foreach($answers as $a)
            <div class="rounded-2xl border border-gray-200/60 bg-white p-4 sm:p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="whitespace-pre-line text-[15px] leading-relaxed text-gray-900 dark:text-gray-100">{{ $a->body }}</p>
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $a->user->name ?? '—' }} · {{ $a->created_at->diffForHumans() }}
                        </div>
                    </div>

                    {{-- Panel głosów (po prawej jak na screenie) --}}
                    <div class="w-20 shrink-0 text-center">
                        <form method="post" action="{{ route('answers.vote', $a) }}" class="vote" data-answer-id="{{ $a->id }}">
                            @csrf
                            <input type="hidden" name="value" value="1">
                            <button type="submit" class="block w-full rounded-lg border px-2 py-1 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">👍</button>
                            <div class="text-sm" data-up>{{ $a->thumbsUpCount() }}</div>
                        </form>
                        <form method="post" action="{{ route('answers.vote', $a) }}" class="vote mt-2" data-answer-id="{{ $a->id }}">
                            @csrf
                            <input type="hidden" name="value" value="-1">
                            <button type="submit" class="block w-full rounded-lg border px-2 py-1 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">👎</button>
                            <div class="text-sm" data-down>{{ $a->thumbsDownCount() }}</div>
                        </form>
                    </div>
                </div>

                {{-- Edycja / usunięcie --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('update', $a)
                        @if(auth()->id()===$a->user_id && $a->created_at->addMinutes(30)->isFuture())
                            <details class="w-full sm:w-auto">
                                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400">Edytuj (30 min)</summary>
                                <form method="post" action="{{ route('answers.update', $a) }}" class="mt-2 space-y-2">
                                    @csrf @method('put')
                                    <textarea name="body" rows="3" class="w-full rounded-xl border px-3 py-2 dark:border-white/10 dark:bg-zinc-800" required>{{ $a->body }}</textarea>
                                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Zapisz</button>
                                </form>
                            </details>
                        @elseif(auth()->user()?->hasAnyRole(['admin','moderator']))
                            <details class="w-full sm:w-auto">
                                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400">Edytuj</summary>
                                <form method="post" action="{{ route('answers.update', $a) }}" class="mt-2 space-y-2">
                                    @csrf @method('put')
                                    <textarea name="body" rows="3" class="w-full rounded-xl border px-3 py-2 dark:border-white/10 dark:bg-zinc-800" required>{{ $a->body }}</textarea>
                                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Zapisz</button>
                                </form>
                            </details>
                        @endif
                    @endcan

                    @can('delete', $a)
                        <form method="post" action="{{ route('answers.destroy', $a) }}" onsubmit="return confirm('Usunąć odpowiedź?');">
                            @csrf @method('delete')
                            <button class="rounded-lg border px-3 py-1 text-sm text-red-600 hover:bg-red-50 dark:border-white/10 dark:hover:bg-white/5">Usuń</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach

        <div>{{ $answers->links() }}</div>
    </div>
</div>

{{-- prosty AJAX do łapek --}}
<script>
document.querySelectorAll('form.vote').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: fd });
    if (res.ok) {
      const json = await res.json();
      const box = form.closest('.w-20');
      box.querySelector('[data-up]').textContent = json.up;
      box.querySelector('[data-down]').textContent = json.down;
    }
  });
});
</script>
@endsection
