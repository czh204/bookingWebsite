<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * The phone format the site accepts — a Malaysian mobile number in
     * international form. Same rule the registration form enforces, kept
     * here as a constant so the two can't drift apart silently.
     */
    protected const PHONE_RULE = 'regex:/^\+60(?:1[02-46-9]\d{7}|11\d{8})$/';

    /**
     * The account settings page.
     *
     * The order summary is read-only context — someone checking their
     * details usually wants to see the account is the one their bookings
     * are on, and it saves a trip to My Bookings to confirm that.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('account.index', [
            'user' => $user,
            'orderCount' => Order::where('user_id', $user->id)->count(),
            'lastOrder' => Order::where('user_id', $user->id)->latest()->first(),
        ]);
    }

    /**
     * Update name, email and phone.
     *
     * Both email and phone are unique columns, so both ignore the current
     * user's own row — otherwise saving the form without changing either
     * would fail validation against the user's own record.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:255'],
            // max:191, not 255: that's the width of the column, and MySQL
            // would reject anything longer at the database instead.
            'email' => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'required', 'string', self::PHONE_RULE,
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ], [
            'phone.regex' => 'Enter a Malaysian mobile number in international format, e.g. +60123456789.',
        ]);

        $user->fill($validated)->save();

        return redirect()
            ->route('account.edit')
            ->with('status_profile', 'Your details have been saved.');
    }

    /**
     * Change the password.
     *
     * `current_password` makes the change require the existing password,
     * so someone who walks up to an unlocked browser can't take the
     * account over. The session id is rotated afterwards for the same
     * reason a login rotates it.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8', 'different:current_password'],
        ], [
            'current_password.current_password' => 'That is not your current password.',
            'password.different' => 'Choose a password different from your current one.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('account.edit')
            ->with('status_password', 'Your password has been changed.');
    }
}
