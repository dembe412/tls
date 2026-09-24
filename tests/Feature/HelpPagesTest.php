<?php

namespace Tests\Feature;

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
            ->assertSee('How do I redeem a bonus?')
            ->assertSee('How much do I earn for inviting friends?');
    }

    public function test_guest_is_asked_to_sign_in_before_claiming(): void
    {
        $this->get(route('bonus'))
            ->assertOk()
            ->assertSee('Sign in to claim')
            ->assertSee(route('login'), false);

        $this->post(route('bonus.redeem'), ['code' => 'TSL1234'])
            ->assertRedirect(route('login'));
    }
}
