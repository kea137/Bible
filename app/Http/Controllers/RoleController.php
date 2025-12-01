<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of users for role management.
     */
    public function index()
    {
        Gate::authorize('update', Role::class);

        $users = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'role_ids' => $user->roles->pluck('id'),
            ];
        });

        $roles = Role::all();

        return Inertia::render('Role Management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Update a user's roles.
     */
    public function updateRoles(Request $request, User $user)
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $oldRoles = $user->roles->pluck('name')->toArray();
        $user->roles()->sync($validated['role_ids']);
        $newRoles = $user->fresh()->roles->pluck('name')->toArray();

        // Log the role update
        \App\Models\ActivityLog::log(
            'role_update',
            "Updated roles for user {$user->name}",
            $user->id,
            [
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles,
                'email' => $user->email,
            ]
        );

        return redirect()->back()->with('success', 'User roles updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        Gate::authorize('delete', $user);

        // Log the user deletion by admin
        \App\Models\ActivityLog::log(
            'user_deletion_by_admin',
            "Admin deleted user {$user->name}",
            $user->id,
            ['email' => $user->email]
        );

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
