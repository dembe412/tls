<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HumanCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_shows_the_robot_check(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('I am not a robot')
            ->assertSee('data-human-check', false);
    }

    public function test_registration_is_rejected_without_the_robot_check(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Robot Runner',
                'password' => 'secret1',
                'password_confirmation' => 'secret1',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('human_token');

        $this->assertDatabaseMissing('users', ['name' => 'Robot Runner']);
    }

    public function test_registration_is_rejected_when_the_honeypot_is_filled(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Trap Filler',
                'password' => 'secret1',
                'password_confirmation' => 'secret1',
                'website' => 'https://spam.example',
                ...$this->humanCheckFields(),
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('human_token');

        $this->assertDatabaseMissing('users', ['name' => 'Trap Filler']);
    }

    public function test_registration_is_rejected_when_the_form_is_submitted_instantly(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->get(route('register'));
        $seed = (string) data_get($this->app['session.store']->get('human_check'), 'seed');

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Too Fast',
                'password' => 'secret1',
                'password_confirmation' => 'secret1',
                'human_token' => strrev($seed),
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('human_token');

        $this->assertDatabaseMissing('users', ['name' => 'Too Fast']);
    }

    public function test_registration_passes_when_the_box_is_ticked(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->post(route('register'), [
            'name' => 'Real Person',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            ...$this->humanCheckFields(),
        ])->assertRedirect(route('account'));

        $this->assertDatabaseHas('users', ['name' => 'Real Person']);
    }
}
