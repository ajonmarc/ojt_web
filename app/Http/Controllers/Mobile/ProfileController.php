<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
 public function profile(Request $request)
    {
        $user = Auth::user();

        if ($request->expectsJson()) {
            // Return JSON for React Native
            return response()->json([
                'auth' => [
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        // Add other fields if needed
                    ],
                ],
                'mustVerifyEmail' => $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
                'status' => session('status'),
            ]);
        }

        // Return Inertia response for web
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
            'auth' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // If email changed and user must verify email, reset verification
        if ($user->email !== $request->email && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }

        $user->save();

        return redirect()->back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'account-deleted');
    }


    /**
 * Update the user's password.
 */
public function updatePassword(Request $request)
{
    $validated = $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    $user = Auth::user();
    $user->password = Hash::make($validated['password']);
    $user->save();

    return redirect()->back()->with('status', 'password-updated');
}
}