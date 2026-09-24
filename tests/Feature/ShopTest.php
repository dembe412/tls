<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_lists_available_locks(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Locks for purchase')
            ->assertSee('TS-20')
            ->assertSee('TS-21')
            ->assertSee('TS-30')
            ->assertSee('VIP1')
            ->assertSee('lock-card-gold', false)
            ->assertSee('Home')
            ->assertSee('Products')
            ->assertSee('News')
            ->assertSee('My account')
            ->assertSee('Team')
            ->assertSee('FAQ')
            ->assertSee('Redeem bonus')
            ->assertSee('Our Community')
            ->assertDontSee('VIP5')
            ->assertDontSee('Marketing benefits');
    }

    public function test_products_page_lists_vip_levels(): void
    {
        $this->get(route('products'))
            ->assertOk()
            ->assertSee('TS-20')
            ->assertSee('VIP1')
            ->assertSee('VIP5')
            ->assertSee('Marketing benefits')
            ->assertSee('20,000 UGX')
            ->assertSee('1,000,000 UGX')
            ->assertSee('Monthly salary is paid on the 1st of each month');
    }

    public function test_member_account_shows_money_totals_and_withdraw_rules(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Total recharge')
            ->assertSee('Total withdraws')
            ->assertSee('Earned daily')
            ->assertSee('Withdraw')
            ->assertSee('Minimum withdraw is 2,000 UGX according to the local Ugandan instructions that govern the financial regulations')
            ->assertDontSee('Other locks available')
            ->assertDontSee('Marketing benefits');
    }

    public function test_guest_can_open_register_for_a_lock(): void
    {
        $product = Product::query()->where('name', 'TS-30')->firstOrFail();

        $this->get(route('register', ['lock' => $product->id]))
            ->assertOk()
            ->assertSee('Your first serious TSL lock is one signup away')
            ->assertSee('TS-30');
    }

    public function test_guest_account_prompts_signup(): void
    {
        $this->get(route('account'))
            ->assertOk()
            ->assertSee('Sign up first');
    }
}
