<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index()
    {
        $items = Group::orderBy('name')->paginate(30);
        return view('admin.data.groups', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);
        Group::create($data);
        return back()->with('ok','Dodano grupę.');
    }

    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);
        $group->update($data);
        return back()->with('ok','Zapisano zmiany.');
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return back()->with('ok','Usunięto grupę.');
    }
}

