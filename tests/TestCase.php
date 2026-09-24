<?php

namespace Tests;

use App\Support\HumanCheck;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Loads the register page and answers its "I am not a robot" check the
     * way the browser does, so registration payloads can be posted in tests.
     *
     * @return array{human_token: string}
     */
    protected function humanCheckFields(): array
    {
        $this->get(route('register'));

        $seed = (string) data_get($this->app['session.store']->get('human_check'), 'seed');

        $this->travel(3)->seconds();

        return ['human_token' => HumanCheck::answerFor($seed)];
    }
}
