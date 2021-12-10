<?php

use Illuminate\Log\Events\MessageLogged;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Tests\TestCase;


it('limits the amount of recorded logs', function () {
    $recorder = new LogRecorder(app(), 200);

    foreach (range(1, 400) as $i) {
        $log = new MessageLogged('info', 'test ' . $i, []);
        $recorder->record($log);
    }

    expect($recorder->getLogMessages())->toHaveCount(200);
    expect($recorder->getLogMessages()[0]['message'])->toBe('test 201');
});

it('does not limit the amount of recorded queries', function () {
    $recorder = new LogRecorder(app());

    foreach (range(1, 400) as $i) {
        $log = new MessageLogged('info', 'test ' . $i, []);
        $recorder->record($log);
    }

    expect($recorder->getLogMessages())->toHaveCount(400);
    expect($recorder->getLogMessages()[0]['message'])->toBe('test 1');
});

it('does not record log containing an exception', function () {
    $recorder = new LogRecorder(app());

    $log = new MessageLogged('info', 'test 1', ['exception' => new Exception('test')]);
    $recorder->record($log);
    $log = new MessageLogged('info', 'test 2', []);
    $recorder->record($log);

    expect($recorder->getLogMessages())->toHaveCount(1);
    expect($recorder->getLogMessages()[0]['message'])->toBe('test 2');
});

it('does not ignore log if exception key does not contain exception', function () {
    $recorder = new LogRecorder(app());

    $log = new MessageLogged('info', 'test 1', ['exception' => 'test']);
    $recorder->record($log);
    $log = new MessageLogged('info', 'test 2', []);
    $recorder->record($log);

    expect($recorder->getLogMessages())->toHaveCount(2);
    expect($recorder->getLogMessages()[0]['message'])->toBe('test 1');
    expect($recorder->getLogMessages()[1]['message'])->toBe('test 2');
    expect($recorder->getLogMessages()[0]['context'])->toBeArray();
    $this->assertArrayHasKey('exception', $recorder->getLogMessages()[0]['context']);
    expect($recorder->getLogMessages()[0]['context']['exception'])->toBe('test');
});
