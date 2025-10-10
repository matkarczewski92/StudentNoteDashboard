<?php

namespace App\Http\Controllers;

use App\Models\{Poll, PollOption, PollVote, Question};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::withCount('votes')->latest()->paginate(20);
        return view('polls.index', compact('polls'));
    }

    public function show(Poll $poll)
    {
        $poll->load(['options' => function($q){ $q->withCount('votes'); }]);
        $userId = Auth::id();
        $userVoteIds = PollVote::where('poll_id', $poll->id)
            ->where('user_id', $userId)
            ->pluck('poll_option_id')
            ->all();
        $firstVoteAt = PollVote::where('poll_id', $poll->id)
            ->where('user_id', $userId)
            ->oldest('created_at')
            ->value('created_at');
        $hasVoted = !empty($userVoteIds);
        $firstVoteAtC = $firstVoteAt ? Carbon::parse($firstVoteAt) : null;
        $editUntil = $firstVoteAtC ? $firstVoteAtC->copy()->addMinutes(10) : null;
        $canEditVote = $hasVoted && $editUntil && now()->lte($editUntil);
        return view('polls.show', compact('poll','userVoteIds','hasVoted','canEditVote','editUntil'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'options' => ['required','array','min:2'],
            'options.*' => ['required','string','max:255'],
            'is_multiple' => ['sometimes','boolean'],
            'question_id' => ['sometimes','integer','exists:questions,id'],
        ]);

        $poll = Poll::create([
            'title' => $data['title'],
            'is_closed' => false,
            'is_multiple' => (bool)($data['is_multiple'] ?? false),
            'user_id' => Auth::id(),
        ]);
        foreach ($data['options'] as $label) {
            if (trim($label) !== '') {
                PollOption::create(['poll_id' => $poll->id, 'label' => trim($label)]);
            }
        }
        // jeżeli przyszło question_id i użytkownik może edytować pytanie — podepnij
        if (!empty($data['question_id'] ?? null)) {
            $question = Question::find($data['question_id']);
            if ($question && (auth()->user()?->can('update', $question))) {
                $question->update(['poll_id' => $poll->id]);
                return redirect()->route('questions.show', $question)->with('ok','Ankieta utworzona i podpięta.');
            }
        }

        return redirect()->route('polls.show', $poll)->with('ok','Ankieta utworzona.');
    }

    public function update(Request $request, Poll $poll)
    {
        // Rozróżnij zamykanie/otwieranie od edycji tytułu/opcji
        if ($request->has('is_closed') && !$request->has('title') && !$request->has('is_multiple')) {
            $this->authorize('close', $poll);
            $data = $request->validate(['is_closed' => ['required','boolean']]);
            $poll->update(['is_closed' => (bool)$data['is_closed']]);
            return back()->with('ok', $poll->is_closed ? 'Ankieta zamknięta.' : 'Ankieta otwarta.');
        }

        $this->authorize('update', $poll);
        $data = $request->validate([
            'title' => ['sometimes','string','max:255'],
            'is_multiple' => ['sometimes','boolean'],
        ]);
        $poll->update($data);
        return back()->with('ok','Zapisano ankietę.');
    }

    public function destroy(Poll $poll)
    {
        $this->authorize('delete', $poll);
        $poll->delete();
        return redirect()->route('polls.index')->with('ok','Usunięto ankietę.');
    }

    public function addOption(Request $request, Poll $poll)
    {
        $this->authorize('update', $poll);
        $data = $request->validate(['label' => ['required','string','max:255']]);
        PollOption::create(['poll_id' => $poll->id, 'label' => trim($data['label'])]);
        return back()->with('ok','Dodano opcję.');
    }

    public function removeOption(Poll $poll, PollOption $option)
    {
        $this->authorize('update', $poll);
        abort_unless($option->poll_id === $poll->id, 404);
        $option->delete();
        return back()->with('ok','Usunięto opcję.');
    }

    public function vote(Request $request, Poll $poll)
    {
        abort_if($poll->is_closed, 403);
        $data = $request->validate([
            'option_id' => ['nullable','integer','exists:poll_options,id'],
            'option_ids' => ['nullable','array'],
            'option_ids.*' => ['integer','exists:poll_options,id'],
            'clear' => ['sometimes','boolean'],
        ]);

        $userId = Auth::id();
        $hasPrior = PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->exists();
        $firstAt = null;
        $editAllowed = true;
        if ($hasPrior) {
            $firstAt = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $userId)
                ->oldest('created_at')
                ->value('created_at');
            $firstAtC = $firstAt ? Carbon::parse($firstAt) : null;
            $editAllowed = $firstAtC && now()->lte($firstAtC->copy()->addMinutes(10));
        }

        // Oblicz żądane opcje
        $selected = [];
        if ($poll->is_multiple) {
            $selected = array_values(array_unique(array_map('intval', $data['option_ids'] ?? [])));
        } else {
            if (isset($data['option_id'])) { $selected = [(int)$data['option_id']]; }
        }

        $clear = (bool)($data['clear'] ?? false);

        if (!$hasPrior && $clear) {
            return response()->json(['error' => 'Brak głosu do usunięcia.'], 422);
        }

        // Edycja istniejącego głosu wymaga okna 10 minut
        if ($hasPrior && !$editAllowed) {
            return response()->json(['error' => 'Czas na edycję głosu minął.'], 403);
        }

        // Jeśli nie ma zaznaczeń i nie ma trybu clear → wymagane opcje dla pierwszego głosu
        if (!$hasPrior && !$selected) {
            return response()->json(['error' => 'Brak wybranych opcji.'], 422);
        }

        // Waliduj opcje względem ankiety
        $validOptionIds = $selected ? PollOption::where('poll_id', $poll->id)
            ->whereIn('id', $selected)->pluck('id')->all() : [];

        if ($clear) {
            PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->delete();
            return $this->stats($poll);
        }

        if ($poll->is_multiple) {
            // Synchronizuj: usuń nadmiarowe, dodaj brakujące
            $keep = $validOptionIds;
            PollVote::where('poll_id', $poll->id)->where('user_id', $userId)
                ->whereNotIn('poll_option_id', $keep)->delete();
            foreach ($keep as $oid) {
                PollVote::firstOrCreate(['poll_id'=>$poll->id,'poll_option_id'=>$oid,'user_id'=>$userId]);
            }
        } else {
            // Pojedynczy wybór: zamień głos; jeśli brak selected → nic (gdy hasPrior to clear używamy)
            PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->delete();
            if (!empty($validOptionIds)) {
                PollVote::firstOrCreate(['poll_id'=>$poll->id,'poll_option_id'=>$validOptionIds[0],'user_id'=>$userId]);
            }
        }

        return $this->stats($poll);
    }

    public function stats(Poll $poll)
    {
        $poll->load('options');
        $counts = PollVote::selectRaw('poll_option_id, COUNT(*) as c')
            ->where('poll_id', $poll->id)
            ->groupBy('poll_option_id')->pluck('c','poll_option_id');
        $total = $counts->sum();

        $options = $poll->options->map(function($opt) use ($counts, $total){
            $c = (int)($counts[$opt->id] ?? 0);
            $pct = $total > 0 ? round(100 * $c / $total) : 0;
            return [
                'id' => $opt->id,
                'label' => $opt->label,
                'votes' => $c,
                'percent' => $pct,
            ];
        })->values();

        return response()->json([
            'total' => (int)$total,
            'options' => $options,
        ]);
    }

    public function me(Poll $poll)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['hasVoted'=>false,'canEditVote'=>false,'editUntil'=>null,'optionIds'=>[],'is_multiple'=>(bool)$poll->is_multiple]);
        }
        $optionIds = PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->pluck('poll_option_id')->all();
        $firstVoteAt = PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->oldest('created_at')->value('created_at');
        $hasVoted = !empty($optionIds);
        $firstVoteAtC = $firstVoteAt ? Carbon::parse($firstVoteAt) : null;
        $editUntil = $firstVoteAtC ? $firstVoteAtC->copy()->addMinutes(10) : null;
        $canEditVote = $hasVoted && $editUntil && now()->lte($editUntil);
        return response()->json([
            'hasVoted' => (bool)$hasVoted,
            'canEditVote' => (bool)$canEditVote,
            'editUntil' => $editUntil ? $editUntil->toIso8601String() : null,
            'optionIds' => $optionIds,
            'is_multiple' => (bool)$poll->is_multiple,
        ]);
    }
}
