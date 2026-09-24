<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Support\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_my_account_lists_every_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('Account balance')
            ->assertSee('Recharge balance')
            ->assertSee('VIP level')
            ->assertSee('ABC level')
            ->assertSee('Purchased products')
            ->assertSee('Recharge history')
            ->assertSee('Invite link')
            ->assertSee('Referral history')
            ->assertSee('Bonuses and rewards')
            ->assertSee('Transaction history')
            ->assertSee('Customer support')
            ->assertSee('Account settings')
            ->assertSee('Log out')
            ->assertSee('Withdraw');
    }

    public function test_my_account_shows_only_products_the_member_bought(): void
    {
        $user = User::factory()->create();
        $bought = Product::query()->where('name', 'TS-20')->firstOrFail();
        $notBought = Product::query()->where('name', 'TS-50')->firstOrFail();

        Wallet::credit($user, Wallet::RECHARGE, (int) $bought->cost_price, 'recharge');
        $this->actingAs($user)->post(route('locks.request', $bought), ['payment_source' => 'recharge']);

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee($bought->name)
            ->assertDontSee($notBought->name);
    }

    public function test_my_account_shows_the_invite_link_and_vip_progress(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee($user->referral_code)
            ->assertSee(route('register', ['ref' => $user->referral_code]), false)
            ->assertSee('Next card');
    }

    public function test_purchase_history_appears_once_a_lock_is_bought(): void
    {
        $user = User::factory()->create();
        $lock = Product::query()->where('name', 'TS-21')->firstOrFail();

        Wallet::credit($user, Wallet::RECHARGE, (int) $lock->cost_price, 'recharge');
        $this->actingAs($user)->post(route('locks.request', $lock), ['payment_source' => 'recharge']);

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Purchase history')
            ->assertSee('Recharge balance');
    }
}
