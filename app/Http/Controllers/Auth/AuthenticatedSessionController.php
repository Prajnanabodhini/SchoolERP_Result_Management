<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

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
        // 1. Validate the input
        $request->validate([
            'school'   => ['required', 'in:chikhali,shirgaon'], // Added school validation
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Find the user by the 'name' column
        $user = User::where('name', $request->username)->first();

        // 3. Check if user exists AND the plain text password matches exactly
        if ($user && $user->password === $request->password) {
            
            // Optional: Block inactive users from logging in
            if (!$user->is_active) {
                return back()->withErrors([
                    'username' => 'Your account is deactivated. Please contact the administrator.',
                ])->onlyInput('username');
            }

            // 4. Log the user in manually
            Auth::login($user, $request->boolean('remember'));
            
            // 5. Regenerate session for security
            $request->session()->regenerate();

            // 6. 🔥 SAVE THE SELECTED SCHOOL TO SESSION 🔥
            // We do this AFTER regenerate() so it doesn't get wiped out
            $request->session()->put('school_code', $request->input('school'));

            return redirect()->route('dashboard');
        }

        // 7. If login fails, return with error
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}