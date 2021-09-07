<?php

namespace Spatie\LaravelIgnition\Tests;

use Exception;
use Flare as FlareFacade;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use Spatie\FlareClient\Flare;
use Spatie\LaravelIgnition\Tests\Mocks\FakeClient;

class FlareTest extends TestCase
{
    protected FakeClient $fakeClient;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('view:clear');

        $this->app['config']['logging.channels.flare'] = [
            'driver' => 'flare',
        ];

        config()->set('logging.channels.flare.driver', 'flare');
        config()->set('logging.default', 'flare');
        config()->set('flare.key', 'some-key');

        $this->fakeClient = new FakeClient();

        $this->app->singleton(Flare::class, fn () => new Flare($this->fakeClient));

        $this->useTime('2019-01-01 12:34:56');

        View::addLocation(__DIR__.'/stubs/views');
    }

    /** @test */
    public function it_can_manually_report_exceptions()
    {
        FlareFacade::report(new Exception());

        $this->fakeClient->assertRequestsSent(1);
    }
}
