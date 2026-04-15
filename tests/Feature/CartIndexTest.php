<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_carts_index(): void
    {
        $response = $this->get(route('carts.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_own_cart_card_on_index_page(): void
    {
        /** @var User $currentUser */
        $currentUser = User::factory()->create([
            'name' => 'Current User',
            'email' => 'current@example.com',
        ]);

        User::factory()->create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        $response = $this
            ->actingAs($currentUser)
            ->get(route('carts.index'));

        $response
            ->assertOk()
            ->assertSeeText('My Cart')
            ->assertSeeText('Current User')
            ->assertSeeText('current@example.com')
            ->assertDontSeeText('Other User')
            ->assertDontSeeText('other@example.com');
    }
}
