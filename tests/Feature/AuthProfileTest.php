<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register_and_sign_in_with_a_username(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->post(route('register'), [
            'name' => 'Aisha Nalwoga',
            'phone' => '0781495461',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            ...$this->humanCheckFields(),
        ])->assertRedirect(route('account'));

        $this->assertDatabaseHas('users', [
            'name' => 'Aisha Nalwoga',
            'phone' => '0781495461',
        ]);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'login' => 'aisha nalwoga',
            'password' => 'secret1',
        ])->assertRedirect(route('account'));
    }

    public function test_member_can_register_and_sign_in_with_a_phone(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->post(route('register'), [
            'phone' => '0781 495 461',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            ...$this->humanCheckFields(),
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'phone' => '0781495461',
        ]);

        $this->post(route('logout'));

        $this->from(route('login'))
            ->post(route('login'), [
                'login' => '0781495461',
                'password' => 'secret1',
            ])
            ->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_member_can_sign_in_with_either_username_or_phone(): void
    {
        User::factory()->create([
            'name' => 'Brian Juuko',
            'phone' => '0771234567',
            'email' => User::phoneToEmail('0771234567'),
        ]);

        $this->post(route('login'), [
            'login' => 'Brian Juuko',
            'password' => 'password',
        ])->assertRedirect();

        $this->post(route('logout'));

        $this->post(route('login'), [
            'login' => '0771234567',
            'password' => 'password',
        ])->assertRedirect();
    }

    public function test_any_member_can_update_their_name_phone_and_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => '0700000001',
            'role' => 'client',
        ]);

        $this->actingAs($user)
            ->post(route('account.profile'), [
                'name' => 'New Name',
                'phone' => '0700000002',
                'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('0700000002', $user->phone);
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_admin_can_update_their_profile_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'name' => 'Manager',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Your profile')
            ->assertSee('Open profile', false);

        $this->actingAs($admin)
            ->post(route('account.profile'), [
                'name' => 'TSL Manager',
                'phone' => $admin->phone,
                'avatar' => UploadedFile::fake()->image('boss.png', 180, 180),
            ])
            ->assertRedirect();

        $this->assertNotNull($admin->fresh()->avatar_path);
    }

    public function test_invalid_profile_updates_keep_the_form_errors(): void
    {
        $user = User::factory()->create(['name' => 'Aisha']);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('account.profile'), [
                'name' => 'A',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['name'], errorBag: 'profile');
    }

    public function test_profile_photo_sits_in_the_header_on_every_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Aisha Nalwoga',
            'role' => 'client',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Open profile', false)
            ->assertSee('Your profile')
            ->assertSee('Sign out');
    }
}
