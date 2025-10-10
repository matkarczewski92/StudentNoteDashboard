@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

    <h1 class="mb-4 text-3xl font-bold">Pytania</h1>

    {{-- Formularz szybkie pytanie – pełna szerokość --}}
    <div class="rounded-2xl border border-gray-200/60 shadow-sm dark:border-white/10 dark:bg-zinc-900 mb-6">
        <form method="post" action="{{ route('questions.store') }}" class="p-4 sm:p-6">
            @csrf
            <label class="block text-sm font-medium mb-2">Szybkie pytanie</label>
            <input name="title" required
                   class="w-full rounded-xl border px-4 py-3 dark:border-white/10 dark:bg-zinc-800"
                   placeholder="Zadaj pytanie…">
            <div class="mt-3 flex justify-end">
                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">
                    Dodaj
                </button>
            </div>
        </form>
    </div>

    {{-- Lista pytań w stylu „karty trójwierszowe” --}}
    <div class="space-y-3">
        @foreach($questions as $q)
        <div class="rounded-2xl  border-gray-200/60 p-4 sm:p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <div class="text-sm font-semibold">
                {{ $q->user->name ?? '—' }}
                <span class="ml-2 font-normal opacity-70">{{ $q->created_at->format('H:i') }}</span>
                    @if($q->is_closed)
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300">Zamknięte</span>
                    @else
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Otwarte</span>
                    @endif
                    💬 <span>{{ $q->answers_count }}</span>
            </div>

            {{-- Wiersz 2: treść pytania (link) --}}
            <div class="mt-1">
                <span
                   class="text-[15px] leading-relaxed underline-offset-2 hover:underline">
                    {{ $q->title }}
                </span>
            </div>

            {{-- Wiersz 3: meta po lewej, akcje po prawej --}}
            <div class="mt-3 flex items-center justify-between">

                <div class="flex items-center gap-2">
                    <a href="{{ route('questions.show', $q) }}#reply"
                       class="rounded-lg  px-3 py-1 text-sm hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Napisz odpowiedź
                    </a>
                    <a href="{{ route('questions.show', $q) }}"
                       class="rounded-lg  px-3 py-1 text-sm hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Otwórz
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $questions->links() }}</div>
</div>
@endsection
