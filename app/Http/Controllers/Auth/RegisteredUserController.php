<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'emp_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string','max:255'],
            'emp_id' => ['required', 'string', 'unique:users'],
            'dept_id' => ['required', 'exists:departments,dept_id'],
            'email' => ['nullable','string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $createRole = Role::firstOrCreate(['name' => 'User']);

        $user = User::create([
            'emp_name' => $request->emp_name,
            'last_name'=> $request->last_name,
            'emp_id'=> $request->emp_id,
            'dept_id'=> $request->dept_id,
            'email' => $request->email ? $request->email : null,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($createRole);

        event(new Registered($user));
        //Auth::login($user);
        return redirect('/login');

        //return redirect(RouteServiceProvider::HOME);
    }
}
