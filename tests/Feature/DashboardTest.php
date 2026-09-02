<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    // Act
    $response = $this->get(route('dashboard'));
    // Assert
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('dashboard'));
    // Assert
    $response->assertOk();
});
