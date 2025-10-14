<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Group};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->query('q',''));
        $groupId = (int) $request->query('group_id', 0);
        $groups = Group::orderBy('name')->get();
        $users = User::with('groups')
            ->when($q !== '', function($query) use ($q){
                $query->where(function($qq) use ($q){
                    $qq->where('name','like',"%$q%")
                       ->orWhere('email','like',"%$q%")
                       ->orWhere('album','like',"%$q%");
                });
            })
            ->when($groupId > 0, function($query) use ($groupId){
                $query->whereHas('groups', fn($g) => $g->where('groups.id', $groupId));
            })
            ->orderBy('name')
            ->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users','q','groups','groupId'));
    }

    public function edit(User $user)
    {
        $groups = Group::orderBy('name')->get();
        // uĹĽyj zaĹ‚adowanej kolekcji, aby uniknÄ…Ä‡ "Column 'id' is ambiguous"
        $userGroupIds = $user->groups->pluck('id')->all();
        return view('admin.users.edit', compact('user','groups','userGroupIds'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email'],
            'album' => ['nullable','string','max:50'],
            'role'  => ['required','in:user,moderator,admin'],
            'groups'=> ['array'],
            'groups.*' => ['integer','exists:groups,id'],
        ]);
        $user->update([
            'name' => $data['name'],
            'email'=> $data['email'],
            'album'=> $data['album'] ?? null,
            'role' => $data['role'],
        ]);
        $user->groups()->sync($request->input('groups', []));
        return redirect()->route('admin.users.edit', $user)->with('ok','Zapisano zmiany uĹĽytkownika.');
    }

    public function resetPassword(User $user)
    {
        $temp = Str::password(10);
        $user->update(['password' => bcrypt($temp)]);
        return back()->with('ok', "Tymczasowe hasĹ‚o dla {$user->name}: $temp");
    }

    public function bulkAddToGroup(Request $request)
    {
        $data = $request->validate([
            'filter_group_id' => ['nullable','integer','exists:groups,id'],
            'q'              => ['nullable','string'],
            'target_group_id'=> ['required','integer','exists:groups,id'],
        ]);
        $q = trim((string)($data['q'] ?? ''));
        $filterGroupId = (int) ($data['filter_group_id'] ?? 0);
        $targetGroupId = (int) $data['target_group_id'];

        $users = User::query()
            ->when($q !== '', function($query) use ($q){
                $query->where(function($qq) use ($q){
                    $qq->where('name','like',"%$q%")
                       ->orWhere('email','like',"%$q%")
                       ->orWhere('album','like',"%$q%");
                });
            })
            ->when($filterGroupId > 0, function($query) use ($filterGroupId){
                $query->whereHas('groups', fn($g) => $g->where('groups.id', $filterGroupId));
            })
            ->get(['id']);

        $ids = $users->pluck('id')->all();
        if (!empty($ids)) {
            $target = Group::find($targetGroupId);
            $target->users()->syncWithoutDetaching($ids);
        }

        return back()->with('ok', 'Dodano uĹĽytkownikĂłw do wybranej grupy.');
    }

    public function destroy(Request $request, User $user)
    {
        // Nie pozwĂłl usunÄ…Ä‡ samego siebie
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => 'Nie moĹĽesz usunÄ…Ä‡ wĹ‚asnego konta.']);
        }

        // Kont administratora nie moĹĽna usuwaÄ‡
        if (($user->role ?? 'user') === 'admin') {
            return back()->withErrors(['user' => 'Nie moĹĽna usunÄ…Ä‡ konta administratora.']);
        }

        // Ewentualnie: zabezpieczenie na ostatniego admina â€” pomijamy jeĹ›li nie wymagane.
        // if ($user->role === 'admin' && User::where('role','admin')->where('id','!=',$user->id)->count() === 0) {
        //     return back()->withErrors(['user' => 'Nie moĹĽna usunÄ…Ä‡ ostatniego administratora.']);
        // }

        $user->delete();
        return redirect()->route('admin.users.index')->with('ok', 'UĹĽytkownik zostaĹ‚ usuniÄ™ty.');
    }
}
