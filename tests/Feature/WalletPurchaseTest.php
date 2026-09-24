<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Support\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_checkout_offers_both_balances(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        Wallet::credit($user, Wallet::RECHARGE, 50000, 'recharge');

        $this->actingAs($user)
            ->get(route('locks.pay', $product))
            ->assertOk()
            ->assertSee('Account balance')
            ->assertSee('Recharge balance')
            ->assertSee('50,000 UGX');
    }

    public function test_member_can_buy_a_lock_with_their_recharge_balance(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();
        $price = (int) $product->cost_price;

        Wallet::credit($user, Wallet::RECHARGE, $price + 5000, 'recharge');

        $this->actingAs($user)
            ->post(route('locks.request', $product), [
                'payment_source' => 'recharge',
            ])
            ->assertRedirect(route('account'));

        $purchase = Purchase::query()->firstOrFail();

        $this->assertSame('active', $purchase->status);
        $this->assertSame(Wallet::PAY_RECHARGE, $purchase->payment_method);
        $this->assertSame($price, (int) $purchase->principal);
        $this->assertNotNull($purchase->activated_at);
        $this->assertSame(5000, $user->fresh()->rechargeBalance());

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'purchase_id' => $purchase->id,
            'wallet' => Wallet::RECHARGE,
            'type' => 'purchase',
            'amount' => -$price,
            'balance_after' => 5000,
        ]);
    }

    public function test_member_can_buy_a_lock_with_their_account_balance(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();
        $price = (int) $product->cost_price;

        Wallet::credit($user, Wallet::ACCOUNT, $price, 'bonus');

        $this->actingAs($user)
            ->post(route('locks.request', $product), [
                'payment_source' => 'account',
            ])
            ->assertRedirect(route('account'));

        $this->assertSame(0, $user->fresh()->accountBalance());
        $this->assertSame(Wallet::PAY_ACCOUNT, Purchase::query()->value('payment_method'));
    }

    public function test_a_balance_that_is_too_small_cannot_buy_the_lock(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-30')->firstOrFail();

        Wallet::credit($user, Wallet::ACCOUNT, 1000, 'bonus');

        $this->actingAs($user)
            ->from(route('locks.pay', $product))
            ->post(route('locks.request', $product), [
                'payment_source' => 'account',
            ])
            ->assertRedirect(route('locks.pay', $product))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('purchases', 0);
        $this->assertSame(1000, $user->fresh()->accountBalance());
    }

    public function test_mobile_money_still_needs_a_transaction_id(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $this->actingAs($user)
            ->from(route('locks.pay', $product))
            ->post(route('locks.request', $product), [
                'payment_source' => 'mobile_money',
                'payment_method' => 'airtel',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('locks.pay', $product))
            ->assertSessionHasErrors('transaction_id');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_locks_bought_from_a_balance_do_not_inflate_cumulative_recharge(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();
        $price = (int) $product->cost_price;

        Wallet::credit($user, Wallet::RECHARGE, $price, 'recharge');

        $this->actingAs($user)->post(route('locks.request', $product), [
            'payment_source' => 'recharge',
        ]);

        $this->assertSame($price, $user->fresh()->cumulativeRecharge());
    }

    public function test_manager_can_add_money_to_a_member_balance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Aisha Nalwoga']);

        $this->actingAs($admin)
            ->post(route('admin.credit', $member), [
                'wallet' => 'recharge',
                'amount' => 75000,
            ])
            ->assertRedirect();

        $this->assertSame(75000, $member->fresh()->rechargeBalance());
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $member->id,
            'wallet' => Wallet::RECHARGE,
            'type' => 'recharge',
            'amount' => 75000,
        ]);
    }

    public function test_clients_cannot_add_money_to_a_balance(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $other = User::factory()->create();

        $this->actingAs($client)
            ->post(route('admin.credit', $other), [
                'wallet' => 'account',
                'amount' => 999999,
            ])
            ->assertRedirect(route('account'));

        $this->assertSame(0, $other->fresh()->accountBalance());
    }
}
