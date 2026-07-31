<?php

use App\Models\Faq;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createFaqAdminRole(): void
{
    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(ApplicationPermissions::all());
}

it('can view the public FAQ page', function () {
    Faq::factory()->create(['question' => 'How do I order?', 'answer' => 'Fill the form.', 'is_active' => true]);

    $this->get('/faq')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('faqs', 1));
});

it('only shows active FAQs on the public page', function () {
    Faq::factory()->create(['question' => 'Visible', 'answer' => 'Yes', 'is_active' => true]);
    Faq::factory()->create(['question' => 'Hidden', 'answer' => 'No', 'is_active' => false]);

    $this->get('/faq')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('faqs', 1)
            ->where('faqs.0.question', 'Visible'));
});

it('orders FAQs by sort order on the public page', function () {
    Faq::factory()->create(['question' => 'Second', 'sort_order' => 2, 'is_active' => true]);
    Faq::factory()->create(['question' => 'First', 'sort_order' => 1, 'is_active' => true]);

    $this->get('/faq')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('faqs.0.question', 'First'));
});

it('requires authentication to access admin FAQs', function () {
    $this->get('/faqs')->assertRedirect('/login');
});

it('requires permission to access admin FAQs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/faqs')
        ->assertForbidden();
});

it('admin can view the FAQ index', function () {
    createFaqAdminRole();
    $user = User::factory()->create();
    $user->assignRole('admin');

    Faq::factory()->count(3)->create();

    $this->actingAs($user)
        ->get('/faqs')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('faqs', 3));
});
