<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load user role (admin or intern)
        $user->load(['admin', 'intern']);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->user_id,
                'email' => $user->email,
                'name' => $user->name,
                'gender' => $user->gender,
                'isAdmin' => $user->isAdmin(),
                'isIntern' => $user->isIntern(),
                'adminId' => $user->admin?->admin_id,
                'internId' => $user->intern?->intern_id,
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|in:male,female,other',
            'role' => 'required|in:admin,intern',
            
            // Intern-specific fields (required if role is intern)
            'university' => 'required_if:role,intern|string|max:50',
            'department' => 'required_if:role,intern|string|max:50',
            'supervisor' => 'required_if:role,intern|string|max:50',
            'start_date' => 'required_if:role,intern|date',
            'phone_num' => 'required_if:role,intern|string|max:20',
            'emergency_contact' => 'required_if:role,intern|string|max:20',
            'emergency_contact_name' => 'required_if:role,intern|string|max:50',
            'address' => 'required_if:role,intern|string|max:50',
        ]);

        // Create user
        $user = User::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'],
        ]);

        // Create role-specific record
        if ($validated['role'] === 'admin') {
            $user->admin()->create([]);
        } else {
            // For intern, we need an admin to assign them to
            // Get first admin or create a default admin
            $defaultAdmin = Admin::first();
            
            if (!$defaultAdmin) {
                // Create a default admin if none exists
                $adminUser = User::create([
                    'email' => 'admin@inturn.com',
                    'name' => 'Default Admin',
                    'password' => Hash::make('admin123'),
                    'gender' => 'other',
                ]);
                $defaultAdmin = $adminUser->admin()->create([]);
            }

            $user->intern()->create([
                'admin_id' => $defaultAdmin->admin_id,
                'university' => $validated['university'],
                'department' => $validated['department'],
                'supervisor' => $validated['supervisor'],
                'start_date' => $validated['start_date'],
                'phone_num' => $validated['phone_num'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'address' => $validated['address'],
                'status' => 'pending',
            ]);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['admin', 'intern']);

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->user_id,
                'email' => $user->email,
                'name' => $user->name,
                'gender' => $user->gender,
                'isAdmin' => $user->isAdmin(),
                'isIntern' => $user->isIntern(),
                'adminId' => $user->admin?->admin_id,
                'internId' => $user->intern?->intern_id,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Logout user (revoke token)
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    /**
     * Get authenticated user
     * GET /api/auth/user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $user->load(['admin', 'intern']);

        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'email' => $user->email,
                'name' => $user->name,
                'gender' => $user->gender,
                'isAdmin' => $user->isAdmin(),
                'isIntern' => $user->isIntern(),
                'adminId' => $user->admin?->admin_id,
                'internId' => $user->intern?->intern_id,
            ],
        ], 200);
    }
}