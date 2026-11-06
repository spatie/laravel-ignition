<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;

it('preserves query durations from Laravel events', function (?float $duration) {
    $recorder = (new QueryRecorder(app()))->start();

    app(Connection::class)->logQuery('select 1', [], $duration);

    expect($recorder->getQueries())->toHaveCount(1)
        ->and($recorder->getQueries()[0]['sql'])->toBe('select 1')
        ->and($recorder->getQueries()[0]['time'])->toBe($duration);
})->with([null, 0.0, 12.75]);

it('limits the amount of recorded queries', function () {
    $recorder = new QueryRecorder(app(), true, 200);
    $connection = app(Connection::class);

    foreach (range(1, 400) as $i) {
        $query = new QueryExecuted('query '.$i, [], time(), $connection);
        $recorder->record($query);
    }

    expect($recorder->getQueries())->toHaveCount(200);
    expect($recorder->getQueries()[0]['sql'])->toBe('query 201');
});

it('does not limit the amount of recorded queries', function () {
    $recorder = new QueryRecorder(app(), true, null);
    $connection = app(Connection::class);

    foreach (range(1, 400) as $i) {
        $query = new QueryExecuted('query '.$i, [], time(), $connection);
        $recorder->record($query);
    }

    expect($recorder->getQueries())->toHaveCount(400);
    expect($recorder->getQueries()[0]['sql'])->toBe('query 1');
});

it('records bindings', function () {
    $recorder = new QueryRecorder(app(), true);
    $connection = app(Connection::class);

    $query = new QueryExecuted('query 1', ['abc' => 123], time(), $connection);
    $recorder->record($query);

    expect($recorder->getQueries())->toHaveCount(1);
    expect($recorder->getQueries()[0]['sql'])->toBe('query 1');
    expect($recorder->getQueries()[0]['bindings'])->toBeArray();
    expect($recorder->getQueries()[0]['sql'])->toBe('query 1');
    expect($recorder->getQueries()[0]['bindings']['abc'])->toBe(123);
});

it('does not record bindings', function () {
    $recorder = new QueryRecorder(app(), false);
    $connection = app(Connection::class);

    $query = new QueryExecuted('query 1', ['abc' => 123], time(), $connection);
    $recorder->record($query);

    expect($recorder->getQueries())->toHaveCount(1);
    expect($recorder->getQueries()[0]['sql'])->toBe('query 1');
    expect($recorder->getQueries()[0]['bindings'])->toBeNull();
});
