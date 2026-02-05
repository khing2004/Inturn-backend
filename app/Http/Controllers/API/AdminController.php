<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Models\Admin;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * get all interns
     * GET /api/admin/interns
     */
    public function getInterns(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $interns = Intern::with(['user', 'admin.user'])
            ->get()
            ->map(function ($intern) {
                return [
                    'id' => $intern->intern_id,
                    'name' => $intern->user->name,
                    'email' => $intern->user->email,
                    'university' => $intern->university,
                    'department' => $intern->department,
                    'supervisor' => $intern->supervisor,
                    'start_date' => $intern->start_date->format('Y-m-d'),
                    'phone_number' => $intern->phone_number,
                    'emergency_contact' => $intern->emergency_contact,
                    'emergency_contact_name' => $intern->emergency_contact_name,
                    'address' => $intern->address,
                    'status' => $intern->status,
                    'admin_name' => $intern->admin->user->name,
                ];
            });

        return response()->json([
            'interns' => $interns,
        ], 200);
    }

    /**
     * create new intern
     * POST /api/admin/interns
     */
    public function createIntern(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'gender' => 'required|in:male,female,other',
            'university' => 'required|string|max:50',
            'department' => 'required|string|max:50',
            'supervisor' => 'required|string|max:50',
            'start_date' => 'required|date',
            'phone_number' => 'required|string|max:20',
            'emergency_contact' => 'required|string|max:20',
            'emergency_contact_name' => 'required|string|max:50',
            'address' => 'required|string|max:50',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        // create user
        $user = User::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'],
        ]);

        // create intern
        $intern = $user->intern()->create([
            'admin_id' => $request->user()->admin->admin_id,
            'university' => $validated['university'],
            'department' => $validated['department'],
            'supervisor' => $validated['supervisor'],
            'start_date' => $validated['start_date'],
            'phone_number' => $validated['phone_number'],
            'emergency_contact' => $validated['emergency_contact'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'address' => $validated['address'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        $intern->load(['user', 'admin.user']);

        return response()->json([
            'message' => 'Intern created successfully',
            'intern' => [
                'id' => $intern->intern_id,
                'name' => $intern->user->name,
                'email' => $intern->user->email,
                'university' => $intern->university,
                'department' => $intern->department,
                'supervisor' => $intern->supervisor,
                'start_date' => $intern->start_date?->format('Y-m-d'),
                'phone_number' => $intern->phone_number,
                'emergency_contact' => $intern->emergency_contact,
                'emergency_contact_name' => $intern->emergency_contact_name,
                'address' => $intern->address,
                'status' => $intern->status,
                'admin_name' => $intern->admin->user->name,
            ],
        ], 201);
    }

    /**
     * update intern
     * PUT /api/admin/interns/{id}
     */
    public function updateIntern(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = Intern::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'university' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:50',
            'supervisor' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        // pdate user info if provided
        if (isset($validated['name'])) {
            $intern->user->update(['name' => $validated['name']]);
        }

        // update intern info
        $intern->update(array_filter([
            'university' => $validated['university'] ?? null,
            'department' => $validated['department'] ?? null,
            'supervisor' => $validated['supervisor'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? null,
        ]));

        $intern->load(['user', 'admin.user']);

        return response()->json([
            'message' => 'Intern updated successfully',
            'intern' => [
                'id' => $intern->intern_id,
                'name' => $intern->user->name,
                'email' => $intern->user->email,
                'university' => $intern->university,
                'department' => $intern->department,
                'supervisor' => $intern->supervisor,
                'start_date' => $intern->start_date?->format('Y-m-d'),
                'phone_number' => $intern->phone_number,
                'emergency_contact' => $intern->emergency_contact,
                'emergency_contact_name' => $intern->emergency_contact_name,
                'address' => $intern->address,
                'status' => $intern->status,
                'admin_name' => $intern->admin->user->name,
            ],
        ], 200);
    }

    /**
     * delete intern
     * DELETE /api/admin/interns/{id}
     */
    public function deleteIntern(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = Intern::findOrFail($id);
        $user = $intern->user;

        // delete intern (cascade will handle related records)
        $intern->delete();
        
        // delete user
        $user->delete();

        return response()->json([
            'message' => 'Intern deleted successfully',
        ], 200);
    }
}