<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index()
    {
        $items = Semester::orderByDesc('id')->paginate(20);
        return view('admin.data.semesters', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);
        Semester::create($data);
        return back()->with('ok','Dodano semestr.');
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'name' => ['required','string'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);
        $semester->update($data);
        return back()->with('ok','Zapisano zmiany.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return back()->with('ok','Usunięto semestr.');
    }
}

