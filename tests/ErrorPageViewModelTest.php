<?php

namespace Spatie\LaravelIgnition\Tests;

use Exception;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Report;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\ErrorPage\ErrorPageViewModel;

class ErrorPageViewModelTest extends TestCase
{
    /** @test */
    public function it_can_encode_invalid_user_data()
    {
        $flareClient = $this->app->make(Flare::class);

        $exception = new Exception('Test Exception');

        /** @var Report $report */
        $report = $flareClient->createReport($exception);

        $report->group('bad-utf8', [
            'name' => 'JohnDoe'.utf8_decode('ø'),
        ]);

        $model = new ErrorPageViewModel($exception, new IgnitionConfig([]), $report, []);

        $this->assertNotEmpty($model->jsonEncode($report->toArray()));
    }
}
