<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('security settings page can be rendered', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
    // Assert
        ->assertOk()
        ->assertSee('Two-factor authentication')
        ->assertSee('Enable 2FA');
});

test('security settings page requires password confirmation when enabled', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->get(route('security.edit'));

    // Assert
    $response->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function () {
    // Arrange
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    // Act
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
    // Assert
        ->assertOk()
        ->assertSee('Update password')
        ->assertDontSee('Two-factor authentication');
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {
    // Arrange
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    // Act
    $component = Livewire::test('pages::settings.security');

    // Assert
    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('password can be updated', function () {
    // Arrange
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    // Act
    $response = Livewire::test('pages::settings.security')
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    // Assert
    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    // Arrange
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    // Act
    $response = Livewire::test('pages::settings.security')
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    // Assert
    $response->assertHasErrors(['current_password']);
});
