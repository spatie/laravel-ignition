<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Orchestra\Testbench\Exceptions\Handler;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Facades\Flare;

it('can see when an exception is handled, meaning it is reported', function () {
    $handler = new class(app()) extends Handler {
        public static Report $report;

        public function report(Throwable $e)
        {
            self::$report = Flare::createReport($e);
        }
    };

    app()->bind(ExceptionHandler::class, fn () => $handler);

    $someTriggeredException = new Exception('This is a test exception');

    report($someTriggeredException);

    expect($handler::$report)->toBeInstanceOf(Report::class);
    expect($handler::$report->toArray())
        ->toHaveKey('handled', true);
});

it('will not mark an exception handled when it is not', function () {
    $someTriggeredException = new Exception('This is a test exception');

    $report = Flare::createReport($someTriggeredException);

    expect($report->toArray())->toHaveKey('handled', null);
});
