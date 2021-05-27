<?php

namespace Spatie\LaravelIgnition\Tests\Commands;

use Spatie\LaravelIgnition\Tests\TestCase;

class TestCommandTest extends TestCase
{
    protected bool $withFlareKey = false;

    public function withFlareKey(): void
    {
        $this->withFlareKey = true;

        $this->refreshApplication();
    }

    /** @test */
    public function it_can_execute_the_test_command_when_a_flare_key_is_present()
    {
        $this->withFlareKey();

        $testResult = $this->artisan('flare:test');

        is_int($testResult)
            ? $this->assertSame(0, $testResult)
            : $testResult->assertExitCode(0);
    }

    protected function getEnvironmentSetUp($app)
    {
        if ($this->withFlareKey) {
            config()->set('flare.key', 'some-key');
        }
    }
}
