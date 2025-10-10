<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        $groups = Group::orderBy('name')->get(['id', 'name']);

        return view('auth.register', compact('groups'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'album'    => ['required', 'string', 'max:20', 'unique:users,album'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed'],

            // >>> grupy
            'groups'   => ['nullable', 'array'],
            'groups.*' => ['integer', 'exists:groups,id'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'album'    => $validated['album'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // >>> przypisz grupy (jeśli nic nie wybrał – pusta tablica)
        $user->groups()->sync($validated['groups'] ?? []);

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
