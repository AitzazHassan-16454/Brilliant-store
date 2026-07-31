<?php

use App\Models\User;

it('shares isFirstLogin for a new user', function () {
    $user = User::factory()->create(['has_seen_welcome' => false]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('isFirstLogin', true));
});

it('does not share isFirstLogin once the welcome tour was completed', function () {
    $user = User::factory()->create(['has_seen_welcome' => true]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('isFirstLogin', false));
});

it('does not share isFirstLogin for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('isFirstLogin', false));
});

it('requires authentication to mark the welcome tour as seen', function () {
    $this->postJson('/welcome/seen')->assertUnauthorized();
});

it('marks the welcome tour as seen for the authenticated user', function () {
    $user = User::factory()->create(['has_seen_welcome' => false]);

    $this->actingAs($user)
        ->postJson('/welcome/seen')
        ->assertNoContent();

    expect($user->fresh()->has_seen_welcome)->toBeTrue();
});
