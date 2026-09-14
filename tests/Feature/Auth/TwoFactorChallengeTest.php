<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

test('two factor challenge redirects to login when not authenticated', function () {
    // Act
    $response = $this->get(route('two-factor.login'));

    // Assert
    $response->assertRedirect(route('login'));
});

test('two factor challenge can be rendered', function () {
    // Arrange
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    // Act
    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Assert
    $response->assertRedirect(route('two-factor.login'));
});
