<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\FlareClient\Flare;
use Spatie\LaravelIgnition\Support\SentReports;
use Spatie\LaravelIgnition\Tests\Mocks\FakeClient;

uses(TestCase::class);

beforeEach(function () {
    config()->set('logging.channels.flare.driver', 'flare');
    config()->set('logging.default', 'flare');
    config()->set('flare.key', 'some-key');

    $this->fakeClient = new FakeClient();

    $currentFlare = app()->make(Flare::class);

    $middleware = $currentFlare->getMiddleware();

    app()->singleton(Flare::class, function () use ($middleware) {
        $flare = new Flare($this->fakeClient, null, []);

        foreach ($middleware as $singleMiddleware) {
            $flare->registerMiddleware($singleMiddleware);
        }

        return $flare;
    });

    $this->useTime('2019-01-01 12:34:56');
});

it('reports exceptions using the flare api', function () {
    Route::get('exception', fn () => nonExistingFunction());

    $response = $this
        ->get('/exception')
        ->assertStatus(500);

    $this->fakeClient->assertRequestsSent(1);
});

it('does not report normal log messages', function () {
    Log::info('this is a log message');
    Log::debug('this is a log message');

    $this->fakeClient->assertRequestsSent(0);
});

it('reports log messages above the specified minimum level', function () {
    Log::error('this is a log message');
    Log::emergency('this is a log message');
    Log::critical('this is a log message');

    $this->fakeClient->assertRequestsSent(3);
});

it('reports different log levels when configured', function () {
    app()['config']['logging.channels.flare.level'] = 'debug';

    Log::debug('this is a log message');
    Log::error('this is a log message');
    Log::emergency('this is a log message');
    Log::critical('this is a log message');

    $this->fakeClient->assertRequestsSent(4);
});

it('can log null values', function () {
    Log::info(null);
    Log::debug(null);
    Log::error(null);
    Log::emergency(null);
    Log::critical(null);

    $this->fakeClient->assertRequestsSent(3);
});

it('adds log messages to the report', function () {
    Route::get('exception', function () {
        Log::info('info log');
        Log::debug('debug log');
        Log::notice('notice log');

        whoops();
    });

    $this->get('/exception');

    $this->fakeClient->assertRequestsSent(1);

    $arguments = $this->fakeClient->requests[0]['arguments'];

    $logs = $arguments['context']['logs'];

    expect($logs)->toHaveCount(3);
});

it('can report an exception with logs', function ($logLevel) {
    app()['config']['flare.send_logs_as_events'] = false;

    Log::log($logLevel, 'log');

    Route::get('exception', function () {
        whoops();
    });

    $this->get('/exception');

    $arguments = $this->fakeClient->requests[0]['arguments'];

    $logs = $arguments['context']['logs'];

    expect($logs)->toHaveCount(1);
    expect($logs[0]['level'])->toEqual($logLevel);
    expect($logs[0]['message'])->toEqual('log');
    expect($logs[0]['context'])->toEqual([]);
})->with('provideMessageLevels');

it('can report an exception with logs with metadata', function () {
    app()['config']['flare.send_logs_as_events'] = false;

    Log::info('log', [
        'meta' => 'data',
    ]);

    Route::get('exception', function () {
        whoops();
    });

    $this->get('/exception');

    $arguments = $this->fakeClient->requests[0]['arguments'];

    $logs = $arguments['context']['logs'];

    expect($logs[0]['context'])->toEqual(['meta' => 'data']);
});

it('will keep sent reports', function () {
    Route::get('exception', fn () => nonExistingFunction());

    $response = $this
        ->get('/exception')
        ->assertStatus(500);

    $this->fakeClient->assertRequestsSent(1);

    expect(app(SentReports::class)->all())->toHaveCount(1);
    expect(\Spatie\LaravelIgnition\Facades\Flare::sentReports()->all())->toHaveCount(1);
});

// Datasets
dataset('provideMessageLevels', [
    ['info'],
    ['notice'],
    ['debug'],
    ['warning'],
    ['error'],
    ['critical'],
    ['emergency'],
]);
