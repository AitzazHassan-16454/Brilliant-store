<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\PermissionGroupService;
use App\Support\ApplicationPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * The permission service helps organize permissions into groups for the UI.
     */
    public function __construct(private PermissionGroupService $permissionService) {}

    /**
     * Admin: Show a list of all roles.
     */
    public function index()
    {
        // Only allow users with permission to view roles
        if (! Auth::user()->can('roles.view')) {
            abort(403);
        }

        return Inertia::render('Role/Index', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Admin: Show the form to create a new role.
     */
    public function create()
    {
        // Only allow users with permission to create roles
        if (! Auth::user()->can('roles.create')) {
            abort(403);
        }

        // Make sure all default permissions exist in the database
        $this->syncPermissions();

        return Inertia::render('Role/Create', [
            'groupedPermissions' => $this->permissionService->groupPermissions(),
        ]);
    }

    /**
     * Admin: Save a new role to the database.
     */
    public function store(Request $request)
    {
        // Only allow users with permission to create roles
        if (! Auth::user()->can('roles.create')) {
            abort(403);
        }

        // Convert the role name to lowercase and remove extra spaces
        $request->merge([
            'name' => Str::lower(trim($request->input('name', ''))),
        ]);

        // Validate the form data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Create the new role
        $role = Role::create([
            'name' => $validatedData['name'],
            'guard_name' => 'web',
        ]);

        // If permissions were selected, assign them to the role
        if (! empty($validatedData['permissions'])) {
            $permissions = Permission::whereIn('id', $validatedData['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Admin: Show the form to edit an existing role.
     */
    public function edit(Role $role)
    {
        // Only allow users with permission to update roles
        if (! Auth::user()->can('roles.update')) {
            abort(403);
        }

        // Make sure all default permissions exist in the database
        $this->syncPermissions();

        return Inertia::render('Role/Edit', [
            'role' => $role,
            'groupedPermissions' => $this->permissionService->groupPermissions(),
            'assignedPermissions' => $role->permissions->pluck('id')->toArray(),
        ]);
    }

    /**
     * Admin: Update an existing role in the database.
     */
    public function update(Request $request, Role $role)
    {
        // Only allow users with permission to update roles
        if (! Auth::user()->can('roles.update')) {
            abort(403);
        }

        // Convert the role name to lowercase and remove extra spaces
        $request->merge([
            'name' => Str::lower(trim($request->input('name', ''))),
        ]);

        // Validate the form data, ignoring the current role's name for the unique check
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Update the role name
        $role->update([
            'name' => $validatedData['name'],
        ]);

        // Update the permissions assigned to this role
        if (! empty($validatedData['permissions'])) {
            $permissions = Permission::whereIn('id', $validatedData['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            // If no permissions were selected, remove all permissions from the role
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Admin: Delete a role from the database.
     */
    public function destroy(Role $role)
    {
        // Only allow users with permission to delete roles
        if (! Auth::user()->can('roles.delete')) {
            abort(403);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Make sure all application permissions exist in the permissions table.
     * This runs before showing the create/edit forms so all permissions are available.
     */
    private function syncPermissions(): void
    {
        // Loop through each permission name defined in the application
        foreach (ApplicationPermissions::all() as $permissionName) {
            // Create the permission if it doesn't already exist
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}
