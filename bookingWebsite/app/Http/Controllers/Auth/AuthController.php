<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the combined login / register page.
     */
    public function show()
    {
        return view('auth.loginRegister');
    }

    /**
     * Handle a login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validateWithBag('login', [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('Wrong email or password!'),
            ])->errorBag('login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle a new registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validateWithBag('register', [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string',  'regex:/^\+60(?:1[02-46-9]\d{7}|11\d{8})$/'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}