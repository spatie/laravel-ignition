<?php

namespace Spatie\Ignition\Tests;

use Spatie\FlareClient\Api;
use Spatie\FlareClient\Glows\Glow;
use Spatie\FlareClient\Report;
use Spatie\Ignition\Facades\Flare;
use Spatie\Ignition\IgnitionServiceProvider;
use Spatie\Ignition\Tests\TestClasses\FakeTime;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Api::$sendInBatches = false;
    }

    protected function getPackageProviders($app)
    {
        config()->set('flare.key', 'dummy-key');

        return [IgnitionServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Flare' => Flare::class,
        ];
    }

    public function useTime(string $dateTime, string $format = 'Y-m-d H:i:s')
    {
        $fakeTime = new FakeTime($dateTime, $format);

        Report::useTime($fakeTime);
        Glow::useTime($fakeTime);
    }
}
