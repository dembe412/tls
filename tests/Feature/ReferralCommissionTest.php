<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReferralEarning;
use App\Models\User;
use App\Support\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_inviter_earns_five_percent_when_their_invitee_buys_a_lock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sponsor = User::factory()->create(['name' => 'Sponsor']);
        $invitee = User::factory()->create(['referred_by_id' => $sponsor->id]);
        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();
        $price = (int) $lock->cost_price;

        $this->actingAs($invitee)->post(route('locks.request', $lock), [
            'payment_source' => 'mobile_money',
            'payment_method' => 'airtel',
            'transaction_id' => 'REF1001',
            'confirmed' => '1',
        ]);

        $purchase = Purchase::query()->firstOrFail();

        $this->assertSame(0, $sponsor->fresh()->accountBalance(), 'Nothing is paid until the lock is switched on.');

        $this->actingAs($admin)->post(route('admin.activate', $purchase))->assertRedirect();

        $expected = (int) floor($price * 5 / 100);

        $this->assertSame($expected, $sponsor->fresh()->accountBalance());
        $this->assertDatabaseHas('referral_earnings', [
            'user_id' => $sponsor->id,
            'member_id' => $invitee->id,
            'purchase_id' => $purchase->id,
            'level' => 'A',
            'rate_percent' => 5,
            'amount' => $expected,
        ]);
    }

    public function test_inviter_earns_ten_percent_on_a_vip_product(): void
    {
        $sponsor = User::factory()->create();
        $invitee = User::factory()->create(['referred_by_id' => $sponsor->id]);
        $vip = Product::query()->where('name', 'VIP1')->firstOrFail();
        $price = (int) $vip->cost_price;

        Wallet::credit($invitee, Wallet::RECHARGE, $price, 'recharge');

        $this->actingAs($invitee)
            ->post(route('locks.request', $vip), ['payment_source' => 'recharge'])
            ->assertRedirect(route('account'));

        $this->assertSame((int) floor($price * 10 / 100), $sponsor->fresh()->accountBalance());
        $this->assertDatabaseHas('referral_earnings', [
            'user_id' => $sponsor->id,
            'level' => 'A',
            'rate_percent' => 10,
        ]);
    }

    public function test_levels_b_and_c_earn_smaller_shares(): void
    {
        $levelC = User::factory()->create();
        $levelB = User::factory()->create(['referred_by_id' => $levelC->id]);
        $levelA = User::factory()->create(['referred_by_id' => $levelB->id]);
        $buyer = User::factory()->create(['referred_by_id' => $levelA->id]);

        $lock = Product::query()->where('name', 'TS-30')->firstOrFail();
        $price = (int) $lock->cost_price;

        Wallet::credit($buyer, Wallet::RECHARGE, $price, 'recharge');

        $this->actingAs($buyer)->post(route('locks.request', $lock), ['payment_source' => 'recharge']);

        $this->assertSame((int) floor($price * 5 / 100), $levelA->fresh()->accountBalance());
        $this->assertSame((int) floor($price * 2 / 100), $levelB->fresh()->accountBalance());
        $this->assertSame((int) floor($price * 1 / 100), $levelC->fresh()->accountBalance());
    }

    public function test_commission_is_paid_only_once_per_purchase(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sponsor = User::factory()->create();
        $invitee = User::factory()->create(['referred_by_id' => $sponsor->id]);
        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();

        $this->actingAs($invitee)->post(route('locks.request', $lock), [
            'payment_source' => 'mobile_money',
            'payment_method' => 'mtn',
            'transaction_id' => 'REF2002',
            'confirmed' => '1',
        ]);

        $purchase = Purchase::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.activate', $purchase));
        $this->actingAs($admin)->post(route('admin.activate', $purchase));

        $expected = (int) floor(((int) $lock->cost_price) * 5 / 100);

        $this->assertSame($expected, $sponsor->fresh()->accountBalance());
        $this->assertSame(1, ReferralEarning::query()->count());
    }

    public function test_a_member_without_an_inviter_pays_no_commission(): void
    {
        $buyer = User::factory()->create(['referred_by_id' => null]);
        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();

        Wallet::credit($buyer, Wallet::RECHARGE, (int) $lock->cost_price, 'recharge');

        $this->actingAs($buyer)->post(route('locks.request', $lock), ['payment_source' => 'recharge']);

        $this->assertDatabaseCount('referral_earnings', 0);
    }

    public function test_referral_earnings_show_on_the_account_page(): void
    {
        $sponsor = User::factory()->create();
        $invitee = User::factory()->create(['name' => 'Aisha Nalwoga', 'referred_by_id' => $sponsor->id]);
        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();

        Wallet::credit($invitee, Wallet::RECHARGE, (int) $lock->cost_price, 'recharge');
        $this->actingAs($invitee)->post(route('locks.request', $lock), ['payment_source' => 'recharge']);

        $this->actingAs($sponsor)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Referral history')
            ->assertSee('Aisha Nalwoga')
            ->assertSee('Level A');
    }
}
