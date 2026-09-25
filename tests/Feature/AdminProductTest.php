<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_create_a_lock_with_a_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'TS-60',
                'code' => '1260',
                'cost_price' => 200000,
                'daily_income' => 4000,
                'duration_days' => 35,
                'tagline' => 'New flagship',
                'sort_order' => 8,
                'image' => UploadedFile::fake()->image('lock.jpg', 400, 400),
            ])
            ->assertRedirect(route('admin.index'));

        $product = Product::query()->where('code', '1260')->first();
        $this->assertNotNull($product);
        $this->assertSame('TS-60', $product->name);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
        $this->assertTrue(
            str_contains($product->imageUrl(), '/media/') || str_contains($product->imageUrl(), '/uploads/')
        );
        $this->assertTrue(
            NewsArticle::query()->where('product_id', $product->id)->where('user_id', $admin->id)->exists()
        );
    }

    public function test_admin_can_edit_an_existing_lock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::query()->where('name', 'TS-20')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'TS-20 Plus',
                'code' => $product->code,
                'cost_price' => 22000,
                'daily_income' => 1100,
                'duration_days' => 35,
                'tagline' => 'Updated starter',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertSame('TS-20 Plus', $product->fresh()->name);
        $this->assertSame(22000, (int) $product->fresh()->cost_price);
    }

    public function test_admin_can_create_a_vip_level_with_a_lock_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'kind' => 'vip',
                'name' => 'VIP6',
                'code' => 'vip6',
                'color' => 'green',
                'cost_price' => 12000000,
                'monthly_salary' => 1500000,
                'member_requirement' => 2000,
                'sort_order' => 6,
                'image' => UploadedFile::fake()->image('vip.jpg', 400, 400),
            ])
            ->assertRedirect(route('admin.index'));

        $vip = Product::query()->where('code', 'vip6')->first();
        $this->assertNotNull($vip);
        $this->assertTrue($vip->isVip());
        $this->assertSame('green', $vip->color);
        $this->assertSame(1500000, (int) $vip->monthly_salary);
        $this->assertNotNull($vip->image_path);
        $this->assertFalse(
            NewsArticle::query()->where('product_id', $vip->id)->exists()
        );
    }

    public function test_client_cannot_manage_products(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.products.create'))
            ->assertRedirect(route('account'));
    }

    public function test_media_route_rejects_path_traversal(): void
    {
        $this->get('/media/../.env')->assertNotFound();
    }
}
