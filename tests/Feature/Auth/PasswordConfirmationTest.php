<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('password.confirm'));

    // Assert
    $response->assertOk();
});
