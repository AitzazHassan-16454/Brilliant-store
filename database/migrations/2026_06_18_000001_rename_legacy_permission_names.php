<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $mapping = [
            'create users' => 'users.create',
            'create products' => 'products.create',
            'update products' => 'products.update',
            'delete products' => 'products.delete',
            // 'manage orders' => 'orders.manage',
        ];

        foreach ($mapping as $oldName => $newName) {
            $oldPermission = Permission::where('name', $oldName)->first();
            if (! $oldPermission) {
                continue;
            }

            $newPermission = Permission::where('name', $newName)->first();

            if (! $newPermission) {
                $oldPermission->update(['name' => $newName]);

                continue;
            }

            $oldPermissionId = $oldPermission->id;
            $newPermissionId = $newPermission->id;

            DB::table('role_has_permissions')
                ->where('permission_id', $oldPermissionId)
                ->chunkById(100, function ($rows) use ($oldPermissionId, $newPermissionId) {
                    foreach ($rows as $row) {
                        DB::table('role_has_permissions')->updateOrInsert([
                            'role_id' => $row->role_id,
                            'permission_id' => $newPermissionId,
                        ]);
                    }

                    DB::table('role_has_permissions')
                        ->where('permission_id', $oldPermissionId)
                        ->delete();
                });

            $oldPermission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $mapping = [
            'users.create' => 'create users',
            'products.create' => 'create products',
            'products.update' => 'update products',
            'products.delete' => 'delete products',
            'orders.manage' => 'manage orders',
        ];

        foreach ($mapping as $currentName => $legacyName) {
            $currentPermission = Permission::where('name', $currentName)->first();
            if (! $currentPermission) {
                continue;
            }

            $legacyPermission = Permission::where('name', $legacyName)->first();

            if (! $legacyPermission) {
                $currentPermission->update(['name' => $legacyName]);

                continue;
            }

            $currentPermissionId = $currentPermission->id;
            $legacyPermissionId = $legacyPermission->id;

            DB::table('role_has_permissions')
                ->where('permission_id', $currentPermissionId)
                ->chunkById(100, function ($rows) use ($currentPermissionId, $legacyPermissionId) {
                    foreach ($rows as $row) {
                        DB::table('role_has_permissions')->updateOrInsert([
                            'role_id' => $row->role_id,
                            'permission_id' => $legacyPermissionId,
                        ]);
                    }

                    DB::table('role_has_permissions')
                        ->where('permission_id', $currentPermissionId)
                        ->delete();
                });

            $currentPermission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
