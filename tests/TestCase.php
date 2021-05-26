<?php

namespace Spatie\LaravelIgnition\Tests;

use Spatie\FlareClient\Glows\Glow;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Facades\Flare;
use Spatie\Ignition\Tests\TestClasses\FakeTime;
use Spatie\LaravelIgnition\IgnitionServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

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
