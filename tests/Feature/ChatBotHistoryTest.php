<?php

use App\Ai\Agents\OrderSupportAgent;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;

it('requires authentication to view chat history', function () {
    $this->getJson('/ai/history')->assertUnauthorized();
});

it('returns an empty history when the user has no conversations', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/ai/history')
        ->assertOk()
        ->assertJson([
            'messages' => [],
            'conversation_id' => null,
        ]);
});

it('restores the latest conversation with its messages', function () {
    $user = User::factory()->create();

    $olderConversation = Conversation::create([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'title' => 'Older chat',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    ConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $olderConversation->id,
        'user_id' => $user->id,
        'agent' => OrderSupportAgent::class,
        'role' => 'user',
        'content' => 'Old message',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
    ]);

    $latestConversation = Conversation::create([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'title' => 'Order help',
    ]);

    ConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $latestConversation->id,
        'user_id' => $user->id,
        'agent' => OrderSupportAgent::class,
        'role' => 'user',
        'content' => 'Where is my order?',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
    ]);

    ConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $latestConversation->id,
        'user_id' => $user->id,
        'agent' => OrderSupportAgent::class,
        'role' => 'assistant',
        'content' => 'Here is a matching product: **Gold Watch**.',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [
            [
                'id' => 'tr-1',
                'name' => 'GetProductInfo',
                'arguments' => [],
                'result' => json_encode([
                    'products' => [
                        ['uid' => 'P-1', 'name' => 'Gold Watch', 'price' => '199.00'],
                    ],
                ]),
            ],
        ],
        'usage' => [],
        'meta' => [],
    ]);

    $this->actingAs($user)
        ->getJson('/ai/history')
        ->assertOk()
        ->assertJson([
            'conversation_id' => $latestConversation->id,
            'messages' => [
                ['role' => 'user', 'content' => 'Where is my order?'],
                [
                    'role' => 'assistant',
                    'content' => 'Here is a matching product: **Gold Watch**.',
                    'products' => [
                        ['uid' => 'P-1', 'name' => 'Gold Watch', 'price' => '199.00'],
                    ],
                ],
            ],
        ]);
});

it('does not expose conversations that belong to other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::create([
        'id' => (string) Str::uuid7(),
        'user_id' => $owner->id,
        'title' => 'Private chat',
    ]);

    ConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $conversation->id,
        'user_id' => $owner->id,
        'agent' => OrderSupportAgent::class,
        'role' => 'user',
        'content' => 'Private order details',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
    ]);

    $this->actingAs($other)
        ->getJson('/ai/history')
        ->assertOk()
        ->assertJson([
            'messages' => [],
            'conversation_id' => null,
        ]);
});
