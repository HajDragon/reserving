<?php

use App\Enums\ApiTokenAbility;

/*
 * Unit test: ApiTokenAbility enum helper methods.
 * Ensures the ability options, values, and labels stay correct
 * so the token management panel always shows the right choices.
 */

test('options returns value-label pairs for all abilities', function () {
    $options = ApiTokenAbility::options();

    expect($options)->toHaveKeys(['products.read', 'products.write']);
    expect($options['products.read'])->toBe('Read products');
    expect($options['products.write'])->toBe('Manage products');
});

test('values returns string values for all abilities', function () {
    $values = ApiTokenAbility::values();

    expect($values)->toContain('products.read');
    expect($values)->toContain('products.write');
    expect($values)->toHaveCount(2);
});

test('labelsFor translates ability strings to readable labels', function () {
    $labels = ApiTokenAbility::labelsFor(['products.read', 'products.write']);

    expect($labels)->toContain('Read products');
    expect($labels)->toContain('Manage products');
});

test('labelsFor gracefully handles unknown ability strings', function () {
    $labels = ApiTokenAbility::labelsFor(['unknown.ability']);

    expect($labels)->toContain('unknown.ability');
});

test('each enum case has a matching label', function () {
    foreach (ApiTokenAbility::cases() as $ability) {
        expect($ability->label())->not->toBeEmpty();
    }
});
