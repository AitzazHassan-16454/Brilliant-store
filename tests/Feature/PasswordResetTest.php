<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('forgot password screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/ForgotPassword', false)
    );
});

test('reset password link can be requested by user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'customer@example.com',
    ]);

    $response = $this->post(route('password.email'), [
        'email' => 'customer@example.com',
    ]);

    $response->assertSessionHas('status');
    $response->assertSessionHas('success');

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class,
        function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            return ! empty($notification->token)
                && str_contains($mail->actionUrl, route('password.reset', ['token' => $notification->token, 'email' => $user->email]));
        }
    );
});

test('reset password link cannot be requested with invalid email format', function () {
    $response = $this->post(route('password.email'), [
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password link request with nonexistent email returns error', function () {
    $response = $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password screen can be rendered with token', function () {
    $user = User::factory()->create([
        'email' => 'reset-user@example.com',
    ]);

    $token = Password::broker()->createToken($user);

    $response = $this->get(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/ResetPassword', false)
        ->where('token', $token)
        ->where('email', $user->email)
    );
});

test('password can be reset with valid token and confirmed password', function () {
    $user = User::factory()->create([
        'email' => 'user-to-reset@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'user-to-reset@example.com',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('success');

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('password cannot be reset with mismatched confirmation', function () {
    $user = User::factory()->create([
        'email' => 'mismatch@example.com',
    ]);

    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'mismatch@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password cannot be reset with short password', function () {
    $user = User::factory()->create([
        'email' => 'short@example.com',
    ]);

    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'short@example.com',
        'password' => '12345',
        'password_confirmation' => '12345',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'invalid-token@example.com',
        'password' => Hash::make('existing-password'),
    ]);

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-dummy-token',
        'email' => 'invalid-token@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('existing-password', $user->fresh()->password))->toBeTrue();
    $this->assertGuest();
});
