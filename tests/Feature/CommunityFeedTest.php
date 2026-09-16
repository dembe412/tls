<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Support\CommunityFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_shows_the_community_card(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Our Community')
            ->assertSee('Be the first face on this wall')
            ->assertSee(route('community.feed'), false);
    }

    public function test_feed_is_public_and_starts_empty(): void
    {
        $this->getJson(route('community.feed'))
            ->assertOk()
            ->assertJsonPath('title', 'Our Community')
            ->assertJsonPath('earner_count', 0)
            ->assertJsonPath('refresh_seconds', 30)
            ->assertJsonPath('member', null);
    }

    public function test_members_appear_only_after_twenty_four_hours(): void
    {
        $lock = Product::query()->where('name', 'TS-21')->firstOrFail();
        $ready = User::factory()->create(['name' => 'Amina Nakato']);
        $tooSoon = User::factory()->create(['name' => 'Brian Juuko']);

        $this->activateLock($ready, $lock, 25);
        $this->activateLock($tooSoon, $lock, 10);

        $feed = $this->getJson(route('community.feed'))->assertOk();

        $feed->assertJsonPath('earner_count', 1)
            ->assertJsonPath('member.name', 'Amina')
            ->assertJsonPath('member.lock', 'TS-21')
            ->assertJsonPath('member.amount', '1,850 UGX');

        $this->assertNotSame('Brian', $feed->json('member.name'));
    }

    public function test_pending_purchases_and_admins_stay_off_the_wall(): void
    {
        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();
        $admin = User::factory()->create(['name' => 'Manager Dembe', 'role' => 'admin']);
        $pending = User::factory()->create(['name' => 'Pending Paul']);

        $this->activateLock($admin, $lock, 30);
        $pending->purchases()->create([
            'product_id' => $lock->id,
            'status' => 'pending',
            'principal' => $lock->cost_price,
            'daily_income' => $lock->purchaseDailyIncome(),
            'duration_days' => $lock->purchaseDurationDays(),
            'activated_at' => now()->subHours(30),
        ]);

        $this->getJson(route('community.feed'))
            ->assertOk()
            ->assertJsonPath('earner_count', 0)
            ->assertJsonPath('member', null);
    }

    public function test_a_member_with_two_locks_appears_once(): void
    {
        $user = User::factory()->create(['name' => 'Sarah Namubiru']);
        $first = Product::query()->where('name', 'TS-20')->firstOrFail();
        $second = Product::query()->where('name', 'TS-30')->firstOrFail();

        $this->activateLock($user, $first, 26);
        $this->activateLock($user, $second, 50);

        $this->getJson(route('community.feed'))
            ->assertOk()
            ->assertJsonPath('earner_count', 1)
            ->assertJsonPath('member.name', 'Sarah');
    }

    public function test_the_feed_loops_after_every_member_has_been_shown(): void
    {
        $this->travelTo(now()->startOfMinute());

        $lock = Product::query()->where('name', 'TS-20')->firstOrFail();
        $names = ['Aisha One', 'Betty Two', 'Clara Three', 'Diana Four', 'Esther Five', 'Faith Six'];

        foreach ($names as $index => $name) {
            $this->activateLock(
                User::factory()->create(['name' => $name]),
                $lock,
                25 + $index
            );
        }

        $firstNames = ['Aisha', 'Betty', 'Clara', 'Diana', 'Esther', 'Faith'];
        $tick = intdiv(now()->timestamp, CommunityFeed::REFRESH_SECONDS);
        $first = $this->getJson(route('community.feed'))->assertOk();
        $this->assertSame($firstNames[$tick % 6], $first->json('member.name'));
        $this->assertSame(6, $first->json('earner_count'));

        $this->travel(CommunityFeed::REFRESH_SECONDS)->seconds();

        $next = $this->getJson(route('community.feed'))->assertOk();
        $this->assertSame($firstNames[($tick + 1) % 6], $next->json('member.name'));
        $this->assertNotSame($first->json('member.name'), $next->json('member.name'));
    }

    private function activateLock(User $user, Product $product, int $hoursAgo): Purchase
    {
        return $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'principal' => $product->cost_price,
            'daily_income' => $product->purchaseDailyIncome(),
            'duration_days' => $product->purchaseDurationDays(),
            'activated_at' => now()->subHours($hoursAgo),
            'matures_at' => now()->addDays($product->purchaseDurationDays()),
        ]);
    }
}
