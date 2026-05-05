<?php

use App\Enums\ApiTokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('non admin cannot access api token management routes', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('cms.api-tokens.index'))
        ->assertForbidden();
});

test('admin can create api tokens for existing users', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    /** @var User $user */
    $user = User::factory()->create([
        'name' => 'Token User',
        'email' => 'token.user@example.com',
    ]);

    $response = actingAs($admin)->post(route('cms.api-tokens.store'), [
        'user_id' => $user->id,
        'name' => 'Integration Token',
        'abilities' => [
            ApiTokenAbility::ProductsRead->value,
            ApiTokenAbility::ProductsWrite->value,
        ],
    ]);

    $response->assertRedirect(route('cms.api-tokens.index'));
    $response->assertSessionHas('generated_token');
    $response->assertSessionHas('generated_token_name', 'Integration Token');
    $response->assertSessionHas('generated_token_user_name', 'Token User');

    expect(DB::table('personal_access_tokens')->where([
        'name' => 'Integration Token',
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ])->exists())->toBeTrue();

    $storedToken = DB::table('personal_access_tokens')
        ->where('name', 'Integration Token')
        ->first();

    expect(json_decode($storedToken->abilities, true))->toBe([
        ApiTokenAbility::ProductsRead->value,
        ApiTokenAbility::ProductsWrite->value,
    ]);
});

test('admin can search and filter api tokens', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    /** @var User $alphaUser */
    $alphaUser = User::factory()->create([
        'name' => 'Alpha User',
        'email' => 'alpha@example.com',
    ]);
    /** @var User $betaUser */
    $betaUser = User::factory()->create([
        'name' => 'Beta User',
        'email' => 'beta@example.com',
    ]);

    $alphaUser->createToken('Alpha Sync', [ApiTokenAbility::ProductsWrite->value]);
    $betaUser->createToken('Beta Sync', [ApiTokenAbility::ProductsRead->value]);

    actingAs($admin)
        ->get(route('cms.api-tokens.index', [
            'search' => 'Alpha',
        ]))
        ->assertOk()
        ->assertSeeText('Alpha Sync')
        ->assertSeeText('Alpha User')
        ->assertDontSeeText('Beta Sync');

    actingAs($admin)
        ->get(route('cms.api-tokens.index', [
            'ability' => ApiTokenAbility::ProductsRead->value,
        ]))
        ->assertOk()
        ->assertSeeText('Beta Sync')
        ->assertDontSeeText('Alpha Sync');
});

test('admin can copy generated token from the page', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($admin)->post(route('cms.api-tokens.store'), [
        'user_id' => $user->id,
        'name' => 'Clipboard Token',
        'abilities' => [ApiTokenAbility::ProductsRead->value],
    ]);

    actingAs($admin)
        ->get(route('cms.api-tokens.index'))
        ->assertOk()
        ->assertSeeText('Copy token')
        ->assertSeeText('Clipboard Token');
});

test('admin can revoke api tokens', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    /** @var User $user */
    $user = User::factory()->create();

    $accessToken = $user->createToken('Revoke Me');

    actingAs($admin)
        ->delete(route('cms.api-tokens.destroy', $accessToken->accessToken))
        ->assertRedirect(route('cms.api-tokens.index'));

    expect(DB::table('personal_access_tokens')->where('id', $accessToken->accessToken->id)->exists())->toBeFalse();
});

test('api product routes require matching token abilities', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Sanctum::actingAs($user, [ApiTokenAbility::ProductsRead->value]);

    get(route('products.index'))
        ->assertOk();

    post(route('products.store'), [
        'asset_tag' => 'ASSET-1000',
        'name' => 'Permission Test Product',
        'description' => null,
        'type' => 'camera',
        'quantity' => 1,
        'available_quantity' => 1,
        'is_active' => true,
        'photo_path' => null,
        'external_link' => null,
    ])->assertForbidden();

    Sanctum::actingAs($user, [ApiTokenAbility::ProductsWrite->value]);

    post(route('products.store'), [
        'asset_tag' => 'ASSET-1001',
        'name' => 'Writable Product',
        'description' => null,
        'type' => 'camera',
        'quantity' => 1,
        'available_quantity' => 1,
        'is_active' => true,
        'photo_path' => null,
        'external_link' => null,
    ])->assertCreated();
});

test('admin sees the reserving admin dropdown links', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('reserving.index'))
        ->assertOk()
        ->assertSeeText('Reserving Admin')
        ->assertSeeText('Order Management')
        ->assertSeeText('API Tokens');
});
