<?php

namespace Tests;

use App\Support\HumanCheck;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstRemoteDatabase();
    }

    /**
     * Never run RefreshDatabase (or any test) against Neon/production Postgres.
     */
    private function guardAgainstRemoteDatabase(): void
    {
        $connection = (string) config('database.default');
        $host = (string) config("database.connections.{$connection}.host");
        $database = (string) config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && in_array($database, [':memory:', ''], true)) {
            return;
        }

        if ($connection === 'pgsql' || str_contains($host, 'neon.tech') || str_contains($host, 'amazonaws.com')) {
            throw new RuntimeException(
                "Refusing to run tests against remote database [{$connection} / {$host} / {$database}]. Use sqlite :memory: only."
            );
        }
    }

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
