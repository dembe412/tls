<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_sent_to_login_before_paying(): void
    {
        $product = Product::query()->where('name', 'TS-30')->firstOrFail();

        $this->get(route('locks.pay', $product))
            ->assertRedirect(route('login'));
    }

    public function test_member_sees_mobile_money_numbers_for_a_lock(): void
    {
        $user = User::factory()->create(['name' => 'Aisha Nalwoga']);
        $product = Product::query()->where('name', 'TS-30')->firstOrFail();

        $this->actingAs($user)
            ->get(route('locks.pay', $product))
            ->assertOk()
            ->assertSee('Airtel Money')
            ->assertSee('MTN Mobile Money')
            ->assertSee('0750000000')
            ->assertSee('0770000000')
            ->assertSee($product->priceLabel())
            ->assertSee('Aisha Nalwoga')
            ->assertSee($user->phone);
    }

    public function test_admin_can_update_mobile_money_details_used_at_checkout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::query()->where('name', 'TS-30')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.payment-methods.update'), [
                'airtel_number' => '0743001481',
                'airtel_name' => 'TSL Airtel',
                'mtn_number' => '0783563733',
                'mtn_name' => 'TSL MTN',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('locks.pay', $product))
            ->assertOk()
            ->assertSee('0743001481')
            ->assertSee('TSL Airtel');

        $this->actingAs($user)
            ->post(route('locks.request', $product), [
                'payment_method' => 'airtel',
                'transaction_id' => 'UPDATED4455',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('account'));

        $this->assertSame('0743001481', Purchase::query()->firstOrFail()->payment_number);
    }

    public function test_member_can_submit_a_transaction_id_with_their_name(): void
    {
        $user = User::factory()->create(['name' => 'Brian Juuko']);
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $this->actingAs($user)
            ->post(route('locks.request', $product), [
                'payment_method' => 'airtel',
                'transaction_id' => 'mm 123abc',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('account'));

        $purchase = Purchase::query()->first();

        $this->assertNotNull($purchase);
        $this->assertSame($user->id, $purchase->user_id);
        $this->assertSame($product->id, $purchase->product_id);
        $this->assertSame('pending', $purchase->status);
        $this->assertSame('airtel', $purchase->payment_method);
        $this->assertSame('0750000000', $purchase->payment_number);
        $this->assertSame('MM123ABC', $purchase->transaction_id);
        $this->assertSame('Brian Juuko', $purchase->payer_name);
        $this->assertSame((int) $product->cost_price, (int) $purchase->principal);
    }

    public function test_transaction_id_cannot_be_reused(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $product = Product::query()->where('name', 'TS-21')->firstOrFail();

        $this->actingAs($first)
            ->post(route('locks.request', $product), [
                'payment_method' => 'mtn',
                'transaction_id' => 'TX9001',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('account'));

        $other = Product::query()->where('name', 'TS-20')->firstOrFail();

        $this->actingAs($second)
            ->from(route('locks.pay', $other))
            ->post(route('locks.request', $other), [
                'payment_method' => 'mtn',
                'transaction_id' => 'TX9001',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('locks.pay', $other))
            ->assertSessionHasErrors('transaction_id');
    }

    public function test_admin_can_verify_payment_and_switch_on_the_lock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['name' => 'Sarah Achieng']);
        $product = Product::query()->where('name', 'TS-31')->firstOrFail();

        $this->actingAs($client)
            ->post(route('locks.request', $product), [
                'payment_method' => 'airtel',
                'transaction_id' => 'AIR4455',
                'confirmed' => '1',
            ]);

        $purchase = Purchase::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Sarah Achieng')
            ->assertSee('AIR4455')
            ->assertSee('Airtel Money');

        $this->actingAs($admin)
            ->post(route('admin.activate', $purchase))
            ->assertRedirect();

        $this->assertSame('active', $purchase->fresh()->status);
        $this->assertNotNull($purchase->fresh()->activated_at);
    }

    public function test_member_can_buy_a_vip_product(): void
    {
        $user = User::factory()->create(['name' => 'Aisha Nalwoga']);
        $vip = Product::query()->where('name', 'VIP1')->firstOrFail();

        $this->actingAs($user)
            ->get(route('locks.pay', $vip))
            ->assertOk()
            ->assertSee('VIP1')
            ->assertSee($vip->priceLabel());

        $this->actingAs($user)
            ->post(route('locks.request', $vip), [
                'payment_method' => 'mtn',
                'transaction_id' => 'VIP8800',
                'confirmed' => '1',
            ])
            ->assertRedirect(route('account'));

        $purchase = Purchase::query()->first();

        $this->assertNotNull($purchase);
        $this->assertSame($vip->id, $purchase->product_id);
        $this->assertSame('pending', $purchase->status);
        $this->assertSame((int) $vip->cost_price, (int) $purchase->principal);
        $this->assertSame($vip->purchaseDailyIncome(), (int) $purchase->daily_income);
    }
}
