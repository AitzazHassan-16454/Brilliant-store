<?php

use App\Ai\Agents\ProductCopywriterAgent;
use App\Models\Category;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createProductAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

it('requires authentication to generate product copy', function () {
    $this->postJson('/products/ai-description', ['name' => 'Gold Ring'])
        ->assertUnauthorized();
});

it('requires permission to generate product copy', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/products/ai-description', ['name' => 'Gold Ring'])
        ->assertForbidden();
});

it('generates structured product copy with the AI agent', function () {
    createProductAdminRole();

    $category = Category::factory()->create(['name' => 'Jewelry']);
    $admin = User::factory()->create()->assignRole('admin');

    ProductCopywriterAgent::fake([[
        'name' => 'Handcrafted Gold Ring',
        'description' => 'A timeless gold ring, handcrafted with precision.',
        'features' => ['18k gold', 'Handcrafted', 'Polished finish'],
        'seo_keywords' => ['gold ring', 'handmade jewelry', 'luxury gift'],
        'meta_title' => 'Handcrafted Gold Ring | Brilliant',
        'meta_description' => 'A timeless handcrafted gold ring from Brilliant Premium Store.',
    ]]);

    $this->actingAs($admin)
        ->postJson('/products/ai-description', [
            'name' => 'Gold Ring',
            'category_id' => $category->id,
            'price' => 199.99,
        ])
        ->assertOk()
        ->assertJsonPath('description', 'A timeless gold ring, handcrafted with precision.')
        ->assertJsonPath('name', 'Handcrafted Gold Ring')
        ->assertJsonCount(3, 'features')
        ->assertJsonCount(3, 'seo_keywords');

    ProductCopywriterAgent::assertPrompted(function ($prompt) {
        return str_contains($prompt->prompt, 'Gold Ring')
            && str_contains($prompt->prompt, 'Jewelry')
            && str_contains($prompt->prompt, '$199.99');
    });
});

it('validates the product name before generating', function () {
    createProductAdminRole();

    $admin = User::factory()->create()->assignRole('admin');

    $this->actingAs($admin)
        ->postJson('/products/ai-description', ['name' => ''])
        ->assertUnprocessable();

    ProductCopywriterAgent::assertNeverPrompted();
});

it('shares the current csrf token with the frontend', function () {
    createProductAdminRole();

    $admin = User::factory()->create()->assignRole('admin');

    $this->actingAs($admin)
        ->get('/CreateProduct')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('csrfToken')
            ->where('csrfToken', csrf_token())
        );
});
