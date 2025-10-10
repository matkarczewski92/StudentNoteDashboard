@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

  <h1 class="mb-4 text-3xl font-bold">Pytania</h1>

  {{-- formularz full width --}}
  <div class="mb-6 rounded-2xl border border-white/10 bg-zinc-900 p-4 sm:p-6 shadow-sm">
    <form method="post" action="{{ route('questions.store') }}" class="space-y-3">
      @csrf
      <label class="block text-sm font-medium text-gray-300">Szybkie pytanie</label>
      <input name="title" required
             class="w-full rounded-xl border border-white/10 bg-zinc-800 px-4 py-3 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
             placeholder="Zadaj pytanie…">
      <div class="text-right">
        <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Dodaj</button>
      </div>
    </form>
  </div>

  {{-- lista pytań --}}
  <div class="space-y-3">
    @foreach($questions as $q)
      <div class="rounded-2xl border border-white/10 bg-zinc-900 p-4 sm:p-5 shadow-sm">
        {{-- wiersz 1: autor + godzina --}}
        <div class="text-sm font-semibold text-gray-100">
          {{ $q->user->name ?? '—' }}
          <span class="ml-2 font-normal text-gray-400">{{ $q->created_at->format('H:i') }}</span>
        </div>

        {{-- wiersz 2: treść pytania (link) --}}
        <div class="mt-1">
          <a href="{{ route('questions.show', $q) }}"
             class="text-[15px] leading-relaxed text-gray-100 underline-offset-2 hover:underline">
            {{ $q->title }}
          </a>
        </div>

        {{-- wiersz 3: meta po lewej, akcje po prawej --}}
        <div class="mt-3 flex items-center justify-between">
          <div class="flex items-center gap-4 text-sm text-gray-400">
            <span class="inline-flex items-center gap-1">
              <span class="opacity-70">💬</span> <span>{{ $q->answers_count }}</span>
            </span>
            @if($q->is_closed)
              <span class="rounded-full bg-red-500/10 px-2 py-0.5 text-xs font-semibold text-red-300">Zamknięte</span>
            @else
              <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-300">Otwarte</span>
            @endif
          </div>

          <div class="flex items-center gap-2">
            <a href="{{ route('questions.show', $q) }}#reply"
               class="rounded-lg border border-white/10 px-3 py-1 text-sm text-gray-200 hover:bg-white/5">Napisz odpowiedź</a>
            <a href="{{ route('questions.show', $q) }}"
               class="rounded-lg border border-white/10 px-3 py-1 text-sm text-gray-200 hover:bg-white/5">Otwórz</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $questions->links() }}</div>
</div>
@endsection
