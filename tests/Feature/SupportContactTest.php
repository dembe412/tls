<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_local_number_becomes_a_tappable_whatsapp_link(): void
    {
        PaymentSetting::query()->create(['key' => 'whatsapp', 'value' => '0740602783']);

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertSee('https://wa.me/256740602783', false)
            ->assertSee('+256 740 602783');
    }

    public function test_a_number_saved_with_spaces_or_a_country_code_still_works(): void
    {
        PaymentSetting::query()->create(['key' => 'whatsapp', 'value' => '+256 740 602783']);

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertSee('https://wa.me/256740602783', false);
    }

    public function test_managers_can_change_the_support_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.payment-methods.update'), [
            'airtel_number' => '0750000000',
            'airtel_name' => 'TSL Smart Locks',
            'mtn_number' => '0770000000',
            'mtn_name' => 'TSL Smart Locks',
            'whatsapp' => '0740602783',
        ]);

        $this->assertSame('0740602783', PaymentSetting::valueFor('whatsapp'));

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertSee('https://wa.me/256740602783', false);
    }

    public function test_the_support_card_is_hidden_until_a_number_is_set(): void
    {
        config(['support.whatsapp' => '']);

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertDontSee('wa.me')
            ->assertSee('Support contact is being set up.');
    }
}
