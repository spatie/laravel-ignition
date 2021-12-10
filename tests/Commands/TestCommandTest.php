<?php

use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can execute the test command when a flare key is present', function () {
    withFlareKey();

    $testResult = $this->artisan('flare:test');

    is_int($testResult)
        ? $this->assertSame(0, $testResult)
        : $testResult->assertExitCode(0);
});

// Helpers
function withFlareKey(): void
{
    test()->withFlareKey = true;

    test()->refreshApplication();
}

function getEnvironmentSetUp($app)
{
    if (test()->withFlareKey) {
        config()->set('flare.key', 'some-key');
    }
}
