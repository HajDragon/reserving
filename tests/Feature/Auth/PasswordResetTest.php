<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    // Act
    $response = $this->get(route('password.request'));

    // Assert
    $response->assertOk();
});

test('reset password link can be requested', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    // Act
    $this->post(route('password.request'), ['email' => $user->email]);

    // Assert
    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    // Act
    $this->post(route('password.request'), ['email' => $user->email]);

    // Assert
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        // Act
        $response = $this->get(route('password.reset', $notification->token));

        // Assert
        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    // Act
    $this->post(route('password.request'), ['email' => $user->email]);

    // Assert
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        // Act
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Assert
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});
