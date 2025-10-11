<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Subject, Semester};
use Illuminate\Http\Request;

class SubjectAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('id')->get();
        $semesterId = (int) $request->query('semester_id', optional($semesters->first())->id);
        $items = Subject::where('semester_id', $semesterId)->orderBy('name')->paginate(30)->withQueryString();
        return view('admin.data.subjects', compact('items','semesters','semesterId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'semester_id' => ['required','integer','exists:semesters,id'],
            'name' => ['required','string','max:255'],
            'code' => ['nullable','string','max:50'],
            'lecturer' => ['nullable','string','max:220'],
            'description' => ['nullable','string'],
        ]);
        Subject::create($data);
        return back()->with('ok','Dodano przedmiot.');
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'code' => ['nullable','string','max:50'],
            'lecturer' => ['nullable','string','max:220'],
            'description' => ['nullable','string'],
        ]);
        $subject->update($data);
        return back()->with('ok','Zapisano zmiany.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return back()->with('ok','Usunięto przedmiot.');
    }
}

