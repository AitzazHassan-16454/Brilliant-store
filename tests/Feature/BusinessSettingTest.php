<?php

use App\Models\BusinessSetting;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createBusinessSettingsAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

function businessSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'site_name' => 'Aurora Store',
        'tagline' => 'Made for you',
        'primary_color' => '#3B82F6',
        'accent_color' => '#1D4ED8',
        'contact_email' => 'hello@aurora.test',
        'contact_hours' => 'Mon - Sat: 9:00 AM - 5:00 PM',
        'footer_description' => 'Thoughtful products for everyday living.',
    ], $overrides);
}

it('requires authentication to view business settings', function () {
    $this->get('/settings')->assertRedirect('/login');
});

it('requires admin access to view business settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings')
        ->assertForbidden();
});

it('shows business settings to an admin', function () {
    createBusinessSettingsAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/settings')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Index', false)
            ->where('settings.site_name', 'Brilliant')
        );
});

it('can update business settings', function () {
    createBusinessSettingsAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->post('/settings', businessSettingsPayload())
        ->assertRedirect();

    $this->assertDatabaseHas('business_settings', [
        'site_name' => 'Aurora Store',
        'tagline' => 'Made for you',
        'primary_color' => '#3B82F6',
        'accent_color' => '#1D4ED8',
    ]);
});

it('shares the saved business settings with storefront pages', function () {
    createBusinessSettingsAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post('/settings', businessSettingsPayload());

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('site.site_name', 'Aurora Store')
            ->where('site.tagline', 'Made for you')
            ->where('site.primary_color', '#3B82F6')
        );
});

it('stores an uploaded logo on the public disk', function () {
    Storage::fake('public');

    createBusinessSettingsAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post('/settings', businessSettingsPayload([
        'logo' => UploadedFile::fake()->image('logo.png', 256, 256),
    ]))->assertRedirect();

    $settings = BusinessSetting::query()->firstOrFail();

    expect($settings->logo)->toStartWith('branding/');
    Storage::disk('public')->assertExists($settings->logo);
});

it('validates color and required fields', function () {
    createBusinessSettingsAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->post('/settings', businessSettingsPayload([
            'site_name' => '',
            'primary_color' => 'blue',
            'accent_color' => 'not-a-color',
        ]))
        ->assertSessionHasErrors(['site_name', 'primary_color', 'accent_color']);
});
