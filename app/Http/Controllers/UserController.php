<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'studentId' => 'required|string|unique:users',
            'studentPhone' => 'required|string',
            'ojtProgram' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        // Hash the password before creating the user
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'student';

        User::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, User $user)
    {
        // First, validate non-password fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'studentId' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'studentPhone' => 'required|string',
            'ojtProgram' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        // Update non-password fields directly using query builder to avoid model events
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'studentId' => $validated['studentId'],
                'studentPhone' => $validated['studentPhone'],
                'ojtProgram' => $validated['ojtProgram'],
                'status' => $validated['status'],
                'updated_at' => now()
            ]);

        // Handle password update separately if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8',
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);
        }

        return redirect()->back();
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back();
    }
}
