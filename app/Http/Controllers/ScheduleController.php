<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Lista wydarzeń i kalendarz miesiąca.
     */
    public function index(Request $request)
    {
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // start i koniec miesiąca
        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $firstDay->locale('pl');
        $lastDay  = (clone $firstDay)->endOfMonth()->endOfDay();

        $user = $request->user();

        // grupy użytkownika
        $userGroupIds = $user->groups()->pluck('groups.id')->all();

        $baseQuery = Event::with('groups', 'user')
            ->whereBetween('deadline', [$firstDay, $lastDay])
            ->orderBy('deadline');

        if (!in_array($user->role, ['admin', 'moderator'], true)) {
            if (!empty($userGroupIds)) {
                $baseQuery->whereHas('groups', function ($q) use ($userGroupIds) {
                    $q->whereIn('groups.id', $userGroupIds);
                });
            } else {
                // brak grup => brak wyników
                $baseQuery->whereRaw('1 = 0');
            }
        }

        $events = $baseQuery->get();

        // do formularza dodawania/edycji:
        $allGroups  = Group::orderBy('name')->get();
        $userGroups = $user->groups()->orderBy('name')->get(['groups.id','groups.name']);

        // Twoje dodatkowe dane do widoku (np. siatka kalendarza)
        $weeks = $this->buildCalendarMatrix($firstDay);

        return view('schedule.index', compact(
            'weeks', 'events', 'userGroups', 'allGroups', 'year', 'month', 'firstDay'
        ));
    }
    /**
     * Zapis nowego wydarzenia.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'deadline'    => ['required', 'date'],
            'group_ids'   => ['array'],            // może być wiele grup
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'description' => ['nullable', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $event = new Event([
            'title'       => $validated['title'],
            'deadline'    => Carbon::parse($validated['deadline']),
            'description' => $validated['description'] ?? null,
        ]);
        $event->deadline = \Carbon\Carbon::parse($validated['deadline']);
        $event->user()->associate($user);
        $event->save();

        // Podpinamy grupy (jeśli wybrano)
        if (!empty($validated['group_ids'])) {
            $event->groups()->sync($validated['group_ids']);
        }

        return back()->with('status', 'Wydarzenie dodane.');
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'deadline'    => ['required', 'date'],
            'group_ids'   => ['array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'description' => ['nullable', 'string'],
        ]);

        $event->title       = $validated['title'];
        $event->deadline    = \Carbon\Carbon::parse($validated['deadline']);
        $event->description = $validated['description'] ?? null;
        $event->save();

        $event->groups()->sync($validated['group_ids'] ?? []);

        return back()->with('status', 'Wydarzenie zaktualizowane.');
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorize('delete', $event);

        $event->groups()->detach();
        $event->delete();

        return back()->with('status', 'Wydarzenie usunięte.');
    }

    /**
     * Zbuduj macierz tygodni dla kalendarza (po 7 dni).
     *
     * @return array<int, array<int, \Carbon\Carbon|null>>
     */
    private function buildCalendarMatrix(Carbon $firstDay): array
    {
        // Początek siatki: poniedziałek tygodnia zawierającego 1-szy dzień miesiąca
        $gridStart = $firstDay->copy()->startOfWeek(Carbon::MONDAY);

        // Koniec siatki: niedziela tygodnia zawierającego ostatni dzień miesiąca
        $gridEnd = $firstDay->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $cursor = $gridStart->copy();
        $weeks = [];
        $week = [];

        while ($cursor <= $gridEnd) {
            // Komórki spoza miesiąca ustawiamy na null (ładniej wygląda)
            $week[] = $cursor->month === $firstDay->month ? $cursor->copy() : null;

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }

        if (!empty($week)) {
            $weeks[] = $week;
        }

        return $weeks;
    }
}
