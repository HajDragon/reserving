<?php

test('api docs page is available', function () {
    // Act
    $response = $this->get('/docs');

    // Assert
    $response->assertOk();
});
