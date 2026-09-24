<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\User;
use Database\Seeders\NewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_a_post_with_a_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Amina Nalwoga',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.news.store'), [
                'title' => 'Weekend restock',
                'body' => 'New locks arrived this morning. Come through.',
                'badge' => 'Post',
                'image' => UploadedFile::fake()->image('post.jpg', 600, 400),
            ])
            ->assertRedirect(route('admin.index'));

        $article = NewsArticle::query()->where('title', 'Weekend restock')->first();
        $this->assertNotNull($article);
        $this->assertSame($admin->id, $article->user_id);
        $this->assertNotNull($article->image_path);

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Weekend restock')
            ->assertSee('Amina Nalwoga');
    }

    public function test_home_news_shows_the_admin_name_and_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Dembe Kato',
            'avatar_path' => 'avatars/boss.jpg',
        ]);

        $this->seed(NewsSeeder::class);

        $this->assertTrue(
            NewsArticle::query()->where('user_id', $admin->id)->exists()
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dembe Kato')
            ->assertSee('Manager')
            ->assertSee('media/avatars/boss.jpg')
            ->assertSee('TS-50 is in stock');
    }
}
