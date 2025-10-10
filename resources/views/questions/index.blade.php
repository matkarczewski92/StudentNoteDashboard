@extends('layouts.app')

@section('content')
<div class="container max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Pytania</h1>
        <form method="post" action="{{ route('questions.store') }}" class="flex gap-2">
            @csrf
            <input name="title" class="border rounded px-3 py-2" placeholder="Szybkie pytanie..." required>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Dodaj</button>
        </form>
    </div>

    @foreach($questions as $q)
    <a href="{{ route('questions.show', $q) }}" class="block border rounded p-3 mb-2 hover:bg-gray-50">
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <div class="font-medium">{{ $q->title }}</div>
                <div class="text-sm text-gray-500">Autor: {{ $q->user->name ?? '—' }} · {{ $q->created_at->diffForHumans() }}</div>
            </div>
            <div class="text-sm text-gray-700 whitespace-nowrap">
                {{ $q->answers_count }} odp.
            </div>
            <div class="text-sm">
                @if($q->is_closed)
                   <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Zamknięte</span>
                @else
                   <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Otwarte</span>
                @endif
            </div>
            <div class="opacity-60">💬</div>
        </div>
    </a>
    @endforeach

    <div class="mt-4">{{ $questions->links() }}</div>
</div>
@endsection
