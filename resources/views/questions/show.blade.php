@extends('layouts.app')

@section('content')
<div class="container max-w-3xl mx-auto space-y-6">

    <div class="border rounded p-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">{{ $question->title }}</h1>
                @if($question->body)
                    <p class="mt-2">{{ $question->body }}</p>
                @endif
                <div class="text-sm text-gray-500 mt-2">
                    Autor: {{ $question->user->name ?? '—' }} · {{ $question->created_at->diffForHumans() }}
                </div>
            </div>

            <div class="space-x-2">
                @can('update', $question)
                    <form method="post" action="{{ route('questions.update', $question) }}" class="inline">
                        @csrf @method('put')
                        <input type="hidden" name="is_closed" value="{{ $question->is_closed ? 0 : 1 }}">
                        <button class="px-3 py-1 border rounded">
                            {{ $question->is_closed ? 'Otwórz' : 'Zamknij' }}
                        </button>
                    </form>
                @endcan
                @can('delete', $question)
                    <form method="post" action="{{ route('questions.destroy', $question) }}" class="inline" onsubmit="return confirm('Usunąć pytanie?');">
                        @csrf @method('delete')
                        <button class="px-3 py-1 border rounded text-red-600">Usuń</button>
                    </form>
                @endcan
            </div>
        </div>

        @can('update', $question)
            @if(auth()->id() === $question->user_id && $question->created_at->addMinutes(30)->isFuture())
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-gray-600">Edytuj pytanie (30 min od dodania)</summary>
                    <form method="post" action="{{ route('questions.update', $question) }}" class="mt-2 space-y-2">
                        @csrf @method('put')
                        <input name="title" class="border rounded w-full px-3 py-2" value="{{ old('title', $question->title) }}" required>
                        <textarea name="body" class="border rounded w-full px-3 py-2" rows="3">{{ old('body', $question->body) }}</textarea>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Zapisz</button>
                    </form>
                </details>
            @endif
        @endcan
    </div>

    {{-- Textarea odpowiedzi (między pytaniem a listą odpowiedzi) --}}
    @if(!$question->is_closed)
    <div class="border rounded p-4">
        <form method="post" action="{{ route('answers.store', $question) }}">
            @csrf
            <label class="block text-sm text-gray-600 mb-1">Twoja odpowiedź</label>
            <textarea name="body" rows="4" class="w-full border rounded px-3 py-2" required>{{ old('body') }}</textarea>
            <div class="mt-2">
                <button class="bg-green-600 text-white px-4 py-2 rounded">Dodaj odpowiedź</button>
            </div>
        </form>
    </div>
    @else
        <div class="p-3 text-sm text-gray-600 bg-gray-50 border rounded">Pytanie jest zamknięte — dodawanie odpowiedzi wyłączone.</div>
    @endif

    {{-- Lista odpowiedzi (najnowsze na górze) --}}
    <div class="space-y-3" id="answers">
        @foreach($answers as $a)
        <div class="border rounded p-3">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="whitespace-pre-line">{{ $a->body }}</p>
                    <div class="text-sm text-gray-500 mt-2">
                        {{ $a->user->name ?? '—' }} · {{ $a->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Voting --}}
                <div class="text-center w-20">
                    <form method="post" action="{{ route('answers.vote', $a) }}" class="vote" data-answer-id="{{ $a->id }}">
                        @csrf
                        <input type="hidden" name="value" value="1">
                        <button type="submit" class="block w-full border rounded mb-1">👍</button>
                        <div class="text-sm" data-up>{{ $a->thumbsUpCount() }}</div>
                    </form>
                    <form method="post" action="{{ route('answers.vote', $a) }}" class="vote" data-answer-id="{{ $a->id }}">
                        @csrf
                        <input type="hidden" name="value" value="-1">
                        <button type="submit" class="block w-full border rounded mt-2">👎</button>
                        <div class="text-sm" data-down>{{ $a->thumbsDownCount() }}</div>
                    </form>
                </div>
            </div>

            {{-- Edycja / usuwanie --}}
            <div class="mt-2 flex gap-2">
                @can('update', $a)
                    @if(auth()->id()===$a->user_id && $a->created_at->addMinutes(30)->isFuture())
                        <details>
                            <summary class="cursor-pointer text-sm text-gray-600">Edytuj (do 30 min)</summary>
                            <form method="post" action="{{ route('answers.update', $a) }}" class="mt-2">
                                @csrf @method('put')
                                <textarea name="body" class="w-full border rounded px-3 py-2" rows="3" required>{{ $a->body }}</textarea>
                                <button class="mt-2 bg-blue-600 text-white px-3 py-1 rounded">Zapisz</button>
                            </form>
                        </details>
                    @elseif(auth()->user()?->hasAnyRole(['admin','moderator']))
                        <details>
                            <summary class="cursor-pointer text-sm text-gray-600">Edytuj</summary>
                            <form method="post" action="{{ route('answers.update', $a) }}" class="mt-2">
                                @csrf @method('put')
                                <textarea name="body" class="w-full border rounded px-3 py-2" rows="3" required>{{ $a->body }}</textarea>
                                <button class="mt-2 bg-blue-600 text-white px-3 py-1 rounded">Zapisz</button>
                            </form>
                        </details>
                    @endif
                @endcan

                @can('delete', $a)
                    <form method="post" action="{{ route('answers.destroy', $a) }}" onsubmit="return confirm('Usunąć odpowiedź?');">
                        @csrf @method('delete')
                        <button class="text-red-600 text-sm border rounded px-2 py-1">Usuń</button>
                    </form>
                @endcan
            </div>
        </div>
        @endforeach

        <div>{{ $answers->links() }}</div>
    </div>
</div>

{{-- bardzo prosty JS do ajaxowego głosowania --}}
<script>
document.querySelectorAll('form.vote').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: fd });
    if (res.ok) {
      const json = await res.json();
      const parent = form.closest('.text-center');
      parent.querySelector('[data-up]').textContent = json.up;
      parent.querySelector('[data-down]').textContent = json.down;
    }
  });
});
</script>
@endsection
