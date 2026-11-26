<?php

use App\Ai\Agents\OrderSupportAgent;
use App\Models\User;

it('validates that message is required for chat', function () {
    $this->postJson('/ai/chat', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

it('streams response successfully from order support agent for guests', function () {
    OrderSupportAgent::fake(['Hello! Welcome to our store.']);

    $response = $this->post('/ai/chat', [
        'message' => 'Hello',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('"type":"text-delta"');
    expect($content)->toContain('Hello!');
    expect($content)->toContain('data: [DONE]');
});

it('allows authenticated users to chat and records conversation', function () {
    OrderSupportAgent::fake(['Sure, I can check that for you!']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/ai/chat', [
        'message' => 'Where is my order?',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)->toContain('"type":"conversation"');
    expect($content)->toContain('"conversation_id"');
    expect($content)->toContain('data: [DONE]');
});
