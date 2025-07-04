<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'emp_id' => ['required', 'string'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt(['emp_id' => $request->emp_id, 'password' => $request->password], $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'emp_id' => __('auth.failed'),
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended(RouteServiceProvider::HOME);

}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
