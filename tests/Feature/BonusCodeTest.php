<?php

namespace Tests\Feature;

use App\Models\BonusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_creates_a_bonus_code_and_gets_a_share_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Aisha Nalwoga']);

        $this->actingAs($admin)
            ->post(route('admin.bonuses.store'), [
                'amount' => 10000,
                'assigned_user_id' => $member->id,
                'note' => 'Weekend reward',
            ])
            ->assertRedirect();

        $bonus = BonusCode::query()->firstOrFail();

        $this->assertSame(10000, (int) $bonus->amount);
        $this->assertSame($member->id, $bonus->assigned_user_id);
        $this->assertNull($bonus->claimed_at);
        $this->assertTrue($bonus->isOpen());
        $this->assertSame(route('bonus.claim', $bonus), $bonus->shareUrl());
    }

    public function test_bonus_code_expires_after_the_configured_window(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.bonuses.store'), ['amount' => 5000]);

        $bonus = BonusCode::query()->firstOrFail();

        $this->assertSame(
            (int) config('support.bonus_code_ttl'),
            (int) $bonus->created_at->diffInSeconds($bonus->expires_at)
        );
    }

    public function test_member_claims_a_bonus_from_the_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $bonus = $this->bonusFor($admin, 7500);

        $this->actingAs($member)
            ->get(route('bonus.claim', $bonus))
            ->assertOk()
            ->assertSee('7,500 UGX')
            ->assertSee('Claim');

        $this->actingAs($member)
            ->post(route('bonus.redeem'), ['code' => $bonus->code])
            ->assertRedirect(route('account'));

        $this->assertSame(7500, $member->fresh()->accountBalance());
        $this->assertSame($member->id, $bonus->fresh()->claimed_by_id);
        $this->assertNotNull($bonus->fresh()->claimed_at);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $member->id,
            'wallet' => 'account',
            'type' => 'bonus',
            'amount' => 7500,
        ]);
    }

    public function test_an_expired_bonus_cannot_be_claimed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $bonus = $this->bonusFor($admin, 5000);

        $this->travel(((int) config('support.bonus_code_ttl')) + 60)->seconds();

        $this->actingAs($member)
            ->from(route('bonus'))
            ->post(route('bonus.redeem'), ['code' => $bonus->code])
            ->assertRedirect(route('bonus'))
            ->assertSessionHasErrors('code');

        $this->assertSame(0, $member->fresh()->accountBalance());
        $this->assertNull($bonus->fresh()->claimed_at);
    }

    public function test_a_bonus_cannot_be_claimed_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = User::factory()->create();
        $second = User::factory()->create();

        $bonus = $this->bonusFor($admin, 4000);

        $this->actingAs($first)->post(route('bonus.redeem'), ['code' => $bonus->code]);

        $this->actingAs($second)
            ->from(route('bonus'))
            ->post(route('bonus.redeem'), ['code' => $bonus->code])
            ->assertSessionHasErrors('code');

        $this->assertSame(4000, $first->fresh()->accountBalance());
        $this->assertSame(0, $second->fresh()->accountBalance());
    }

    public function test_a_targeted_bonus_cannot_be_claimed_by_another_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        $stranger = User::factory()->create();

        $bonus = $this->bonusFor($admin, 6000, $target);

        $this->actingAs($stranger)
            ->from(route('bonus'))
            ->post(route('bonus.redeem'), ['code' => $bonus->code])
            ->assertSessionHasErrors('code');

        $this->assertSame(0, $stranger->fresh()->accountBalance());

        $this->actingAs($target)
            ->post(route('bonus.redeem'), ['code' => $bonus->code])
            ->assertRedirect(route('account'));

        $this->assertSame(6000, $target->fresh()->accountBalance());
    }

    public function test_a_guest_who_opens_the_link_is_sent_back_to_it_after_signing_in(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Aisha Nalwoga',
            'phone' => '0781495461',
        ]);

        $bonus = $this->bonusFor($admin, 3000);

        $this->post(route('logout'));

        $this->get(route('bonus.claim', $bonus))
            ->assertOk()
            ->assertSee('Sign in and claim');

        $this->post(route('login'), [
            'login' => $member->phone,
            'password' => 'password',
        ])->assertRedirect(route('bonus.claim', $bonus));
    }

    public function test_clients_cannot_create_bonus_codes(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->post(route('admin.bonuses.store'), ['amount' => 999999])
            ->assertRedirect(route('account'));

        $this->assertDatabaseCount('bonus_codes', 0);
    }

    private function bonusFor(User $admin, int $amount, ?User $assignedTo = null): BonusCode
    {
        $this->actingAs($admin)->post(route('admin.bonuses.store'), array_filter([
            'amount' => $amount,
            'assigned_user_id' => $assignedTo?->id,
        ]));

        return BonusCode::query()->latest('id')->firstOrFail();
    }
}
