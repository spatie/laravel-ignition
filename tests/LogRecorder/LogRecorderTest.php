<?php

use Exception;
use Illuminate\Log\Events\MessageLogged;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('limits the amount of recorded logs', function () {
    $recorder = new LogRecorder(app(), 200);

    foreach (range(1, 400) as $i) {
        $log = new MessageLogged('info', 'test ' . $i, []);
        $recorder->record($log);
    }

    $this->assertCount(200, $recorder->getLogMessages());
    $this->assertSame('test 201', $recorder->getLogMessages()[0]['message']);
});

it('does not limit the amount of recorded queries', function () {
    $recorder = new LogRecorder(app());

    foreach (range(1, 400) as $i) {
        $log = new MessageLogged('info', 'test ' . $i, []);
        $recorder->record($log);
    }

    $this->assertCount(400, $recorder->getLogMessages());
    $this->assertSame('test 1', $recorder->getLogMessages()[0]['message']);
});

it('does not record log containing an exception', function () {
    $recorder = new LogRecorder(app());

    $log = new MessageLogged('info', 'test 1', ['exception' => new Exception('test')]);
    $recorder->record($log);
    $log = new MessageLogged('info', 'test 2', []);
    $recorder->record($log);

    $this->assertCount(1, $recorder->getLogMessages());
    $this->assertSame('test 2', $recorder->getLogMessages()[0]['message']);
});

it('does not ignore log if exception key does not contain exception', function () {
    $recorder = new LogRecorder(app());

    $log = new MessageLogged('info', 'test 1', ['exception' => 'test']);
    $recorder->record($log);
    $log = new MessageLogged('info', 'test 2', []);
    $recorder->record($log);

    $this->assertCount(2, $recorder->getLogMessages());
    $this->assertSame('test 1', $recorder->getLogMessages()[0]['message']);
    $this->assertSame('test 2', $recorder->getLogMessages()[1]['message']);
    $this->assertIsArray($recorder->getLogMessages()[0]['context']);
    $this->assertArrayHasKey('exception', $recorder->getLogMessages()[0]['context']);
    $this->assertSame('test', $recorder->getLogMessages()[0]['context']['exception']);
});
