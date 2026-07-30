<?php

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $permissions = collect([
        'roles.view',
        'roles.create',
        'roles.update',
        'roles.delete',
        'products.create',
    ])->map(fn (string $permission) => Permission::firstOrCreate([
        'name' => $permission,
        'guard_name' => 'web',
    ]));

    $adminRole = Role::query()->firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
    );

    $adminRole->syncPermissions($permissions);
});

test('can view roles index', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('roles.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Role/Index', false)
        ->has('roles')
    );
});

test('can view create role page', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('roles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Role/Create', false)
    );
});

test('can create a role', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->post(route('roles.store'), [
        'name' => 'Editor',
    ]);

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseHas('roles', [
        'name' => 'editor',
        'guard_name' => 'web',
    ]);
});

test('requires name when creating role', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->post(route('roles.store'), []);

    $response->assertSessionHasErrors('name');
});

test('requires unique name when creating role', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');
    Role::factory()->create(['name' => 'Editor']);

    $response = $this->actingAs($user)->post(route('roles.store'), [
        'name' => 'editor',
    ]);

    $response->assertSessionHasErrors('name');
});

test('can view edit role page', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');
    $role = Role::factory()->create();

    $response = $this->actingAs($user)->get(route('roles.edit', $role));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Role/Edit', false)
        ->has('role')
    );
});

test('can update a role', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');
    $role = Role::factory()->create();

    $response = $this->actingAs($user)->put(route('roles.update', $role), [
        'name' => 'Updated Editor',
    ]);

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'updated editor',
    ]);
});

test('can delete a role', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');
    $role = Role::factory()->create();

    $response = $this->actingAs($user)->delete(route('roles.destroy', $role));

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('role permissions allow non admin users to access granted features', function () {
    $role = Role::factory()->create(['name' => 'product manager']);
    $role->givePermissionTo('products.create');

    $user = User::factory()->create(['role' => 'product manager']);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/CreateProduct')
        ->assertOk();
});

test('users without granted permissions are forbidden from protected features', function () {
    $role = Role::factory()->create(['name' => 'viewer']);

    $user = User::factory()->create(['role' => 'viewer']);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/CreateProduct')
        ->assertForbidden();
});

test('admin role can view dashboard even before dashboard permission is seeded', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

test('admin role has every application permission through the gate and shared props', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('admin');

    expect($user->can('products.view'))->toBeTrue()
        ->and($user->can('categories.delete'))->toBeTrue()
        ->and($user->can('orders.update'))->toBeTrue();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('permissions.can', fn ($permissions) => $permissions['products.view'] === true
                && $permissions['categories.delete'] === true
                && $permissions['orders.update'] === true
            )
        );
});
