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
        $users = User::with('groups')
            ->when($q !== '', function($query) use ($q){
                $query->where(function($qq) use ($q){
                    $qq->where('name','like',"%$q%")
                       ->orWhere('email','like',"%$q%")
                       ->orWhere('album','like',"%$q%");
                });
            })
            ->orderBy('name')
            ->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users','q'));
    }

    public function edit(User $user)
    {
        $groups = Group::orderBy('name')->get();
        // użyj załadowanej kolekcji, aby uniknąć "Column 'id' is ambiguous"
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
        return redirect()->route('admin.users.edit', $user)->with('ok','Zapisano zmiany użytkownika.');
    }

    public function resetPassword(User $user)
    {
        $temp = Str::password(10);
        $user->update(['password' => bcrypt($temp)]);
        return back()->with('ok', "Tymczasowe hasło dla {$user->name}: $temp");
    }
}
