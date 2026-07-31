<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Admin: Show a list of all users.
     */
    public function users()
    {
        // Only allow users with permission to view users
        if (! Auth::user()->can('users.view')) {
            abort(403);
        }

        return Inertia::render('Products/Users', [
            'users' => User::all(),
        ]);
    }

    /**
     * Admin: Show the form to create a new user.
     */
    public function createUser()
    {
        // Only allow users with permission to create users
        if (! Auth::user()->can('users.create')) {
            abort(403);
        }

        // Get all roles and format them for a dropdown
        $roles = Role::query()
            ->orderBy('name')
            ->get(['name'])
            ->mapWithKeys(static function ($role) {
                // Convert "some-role" to "Some Role"
                $formattedName = Str::title(str_replace(['-', '_'], ' ', $role->name));

                return [
                    $role->name => $formattedName,
                ];
            });

        return Inertia::render('Products/CreateUser', [
            'roles' => $roles,
        ]);
    }

    /**
     * Admin: Save a new user to the database.
     */
    public function storeUser(Request $request)
    {
        // Only allow users with permission to create users
        if (! Auth::user()->can('users.create')) {
            abort(403);
        }

        // Validate the form data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        // Create the new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'],
        ]);

        // Assign the selected role to the user
        $user->assignRole($validatedData['role']);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Register a new user account.
     */
    public function register(Request $request)
    {
        // Validate the registration data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'max:255', 'confirmed'],
        ]);

        // Create the new user with a default "user" role
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => 'user',
        ]);

        // Assign the "user" role if it exists in the database
        if (Role::where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        return redirect('/');
    }

    /**
     * Log in an existing user.
     */
    public function login(Request $request)
    {
        // Validate the login credentials
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Try to log in with the given email and password
        if (Auth::attempt($credentials)) {
            // Regenerate the session to prevent session fixation attacks
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect admin users to the admin home
            if ($user->role === 'admin') {
                return redirect()->route('home');
            }

            // Redirect regular users to the home page
            return redirect()->route('home');
        }

        // If login fails, go back with an error
        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    /**
     * Mark the welcome tutorial as seen for the authenticated user.
     */
    public function markWelcomeSeen(Request $request)
    {
        $request->user()->update(['has_seen_welcome' => true]);

        return response()->noContent();
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        // Log the user out
        Auth::logout();

        // Clear the session data
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Update the logged-in user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validate the profile data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'password' => ['nullable', 'min:6', 'confirmed'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle the avatar image upload
        if ($request->hasFile('avatar')) {
            // Delete the old avatar if it exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Save the new avatar image
            $validatedData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            // If no new avatar was uploaded, remove avatar from the update data
            unset($validatedData['avatar']);
        }

        // If a new password was provided, it will be hashed by the model
        // If no password was provided, remove it from the update data so it stays the same
        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }

        // Save the updated profile
        $user->update($validatedData);

        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }
}
