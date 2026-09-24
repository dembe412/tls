<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamWithdrawTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_team_tab_lists_referral_rates_and_vip_board(): void
    {
        $this->get(route('team'))
            ->assertOk()
            ->assertSee('Level A')
            ->assertSee('5% locks')
            ->assertSee('10% VIP')
            ->assertSee('Level B')
            ->assertSee('2% commission')
            ->assertSee('Level C')
            ->assertSee('1% commission')
            ->assertSee('Marketing benefits')
            ->assertSee('VIP1');
    }

    public function test_registration_attaches_a_referrer(): void
    {
        User::factory()->create(['role' => 'admin']);
        $sponsor = User::factory()->create(['name' => 'Sponsor']);

        $this->post(route('register'), [
            'name' => 'Invitee',
            'phone' => '0700111222',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            'ref' => $sponsor->referral_code,
            ...$this->humanCheckFields(),
        ])->assertRedirect(route('account'));

        $this->assertSame($sponsor->id, User::query()->where('phone', '0700111222')->value('referred_by_id'));
    }

    public function test_member_sees_an_invite_link_on_team(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('team'))
            ->assertOk()
            ->assertSee($user->referral_code)
            ->assertSee($user->inviteUrl());
    }

    public function test_new_member_is_ordinary_until_they_recharge(): void
    {
        $user = User::factory()->create();

        $this->assertSame('Ordinary', $user->vipRankLabel());

        $this->actingAs($user)
            ->get(route('account'))
            ->assertSee('Ordinary');
    }

    public function test_investor_becomes_vip_zero(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'pending',
            'principal' => $product->cost_price,
            'daily_income' => $product->daily_income,
            'duration_days' => 35,
        ]);

        $this->assertSame('VIP 0', $user->fresh()->vipRankLabel());
    }

    public function test_cash_out_below_two_thousand_is_blocked(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $purchase = Purchase::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'principal' => $product->cost_price,
            'daily_income' => 500,
            'duration_days' => 3,
            'activated_at' => now()->subDays(3),
            'matures_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('account'))
            ->post(route('purchases.cash-out', $purchase))
            ->assertRedirect(route('account'))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('withdrawals', 0);
    }

    public function test_matured_lock_can_cash_out_when_above_the_minimum(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $purchase = Purchase::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'principal' => $product->cost_price,
            'daily_income' => 1000,
            'duration_days' => 3,
            'activated_at' => now()->subDays(3),
            'matures_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('purchases.cash-out', $purchase))
            ->assertRedirect();

        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $user->id,
            'purchase_id' => $purchase->id,
            'amount' => 3000,
            'status' => 'awaiting_approval',
        ]);
    }
}
