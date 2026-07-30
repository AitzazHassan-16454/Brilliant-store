<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ApplicationPermissions::all();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $salemanRole = Role::firstOrCreate(['name' => 'saleman']);

        $adminRole->syncPermissions($permissions);

        $salemanRole->givePermissionTo([
            'dashboard.view',
            'orders.view',
            'orders.update',
            'reviews.view',
            'subcategories.view',
            // 'orders.manage',
        ]);

        $name = env('ADMIN_NAME', 'Admin User');
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_admin' => true,
            ]
        );

        $admin->update([
            'name' => $name,
            'role' => 'admin',
            'is_admin' => true,
        ]);

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $admin->syncRoles(['admin']);
    }
}
