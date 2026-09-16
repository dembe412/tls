<?php

namespace Tests\Feature;

use App\Models\BonusRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_faq_and_redeem_bonus_buttons(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('FAQ')
            ->assertSee('Frequently asked questions')
            ->assertSee('Redeem bonus')
            ->assertSee(route('faq'), false)
            ->assertSee(route('bonus'), false)
            ->assertSee('images/gift.png');
    }

    public function test_faq_page_lists_common_questions(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('Frequently asked questions')
            ->assertSee('How do I buy a lock?')
            ->assertSee('How do I redeem a bonus?');
    }

    public function test_guest_is_asked_to_sign_in_before_redeeming(): void
    {
        $this->get(route('bonus'))
            ->assertOk()
            ->assertSee('Sign in to redeem')
            ->assertSee(route('login'), false);

        $this->post(route('bonus.redeem'), ['code' => 'TSL1234'])
            ->assertRedirect(route('login'));
    }

    public function test_member_can_submit_a_bonus_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('bonus'))
            ->post(route('bonus.redeem'), ['code' => 'tsl-99'])
            ->assertRedirect(route('bonus'));

        $this->assertDatabaseHas('bonus_redemptions', [
            'user_id' => $user->id,
            'code' => 'TSL-99',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('bonus'))
            ->assertSee('TSL-99')
            ->assertSee('Waiting for TSL');
    }

    public function test_member_cannot_submit_the_same_bonus_code_twice(): void
    {
        $user = User::factory()->create();
        BonusRedemption::factory()->create([
            'user_id' => $user->id,
            'code' => 'TSL100',
        ]);

        $this->actingAs($user)
            ->from(route('bonus'))
            ->post(route('bonus.redeem'), ['code' => 'TSL100'])
            ->assertRedirect(route('bonus'))
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_apply_a_pending_bonus_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $redemption = BonusRedemption::factory()->create(['code' => 'TSL200']);

        $this->actingAs($admin)
            ->post(route('admin.bonuses.settle', $redemption), ['status' => 'applied'])
            ->assertRedirect();

        $this->assertSame('applied', $redemption->fresh()->status);
    }
}
