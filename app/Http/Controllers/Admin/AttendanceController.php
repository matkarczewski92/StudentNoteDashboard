<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isStaff = in_array($user->role ?? 'user', ['admin','moderator'], true);
        $groups = $isStaff
            ? Group::withCount('users')->orderBy('name')->get()
            : $user->groups()->withCount('users')->orderBy('name')->get();
        return view('admin.attendance.index', compact('groups'));
    }

    public function print(Request $request)
    {
        $user = $request->user();
        $isStaff = in_array($user->role ?? 'user', ['admin','moderator'], true);

        // Gdy brak parametru i user ma jedną grupę – wydruk tej grupy
        $gid = $request->integer('group_id');
        if (!$gid) {
            if (!$isStaff) {
                $first = $user->groups()->orderBy('name')->value('groups.id');
                if ($first) { $gid = (int)$first; }
            }
        }
        if (!$gid) {
            return redirect()->route('attendance.index')->withErrors(['group_id' => 'Wybierz grupę.']);
        }

        // Weryfikacja dostępu
        if (!$isStaff) {
            $belongs = $user->groups()->where('groups.id', $gid)->exists();
            if (!$belongs) {
                return redirect()->route('attendance.index')->withErrors(['group_id' => 'Brak dostępu do wybranej grupy.']);
            }
        }

        $group = Group::with(['users' => function($q){ $q->orderBy('name'); }])->findOrFail($gid);
        $title = 'Lista obecności — '.$group->name;
        return view('admin.attendance.print', compact('group','title'));
    }
}
