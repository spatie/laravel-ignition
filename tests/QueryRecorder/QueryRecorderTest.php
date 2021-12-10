<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('limits the amount of recorded queries', function () {
    $recorder = new QueryRecorder(app(), true, 200);
    $connection = app(Connection::class);

    foreach (range(1, 400) as $i) {
        $query = new QueryExecuted('query '.$i, [], time(), $connection);
        $recorder->record($query);
    }

    $this->assertCount(200, $recorder->getQueries());
    $this->assertSame('query 201', $recorder->getQueries()[0]['sql']);
});

it('does not limit the amount of recorded queries', function () {
    $recorder = new QueryRecorder(app(), true);
    $connection = app(Connection::class);

    foreach (range(1, 400) as $i) {
        $query = new QueryExecuted('query '.$i, [], time(), $connection);
        $recorder->record($query);
    }

    $this->assertCount(400, $recorder->getQueries());
    $this->assertSame('query 1', $recorder->getQueries()[0]['sql']);
});

it('records bindings', function () {
    $recorder = new QueryRecorder(app(), true);
    $connection = app(Connection::class);

    $query = new QueryExecuted('query 1', ['abc' => 123], time(), $connection);
    $recorder->record($query);

    $this->assertCount(1, $recorder->getQueries());
    $this->assertSame('query 1', $recorder->getQueries()[0]['sql']);
    $this->assertIsArray($recorder->getQueries()[0]['bindings']);
    $this->assertSame('query 1', $recorder->getQueries()[0]['sql']);
    $this->assertSame(123, $recorder->getQueries()[0]['bindings']['abc']);
});

it('does not record bindings', function () {
    $recorder = new QueryRecorder(app(), false);
    $connection = app(Connection::class);

    $query = new QueryExecuted('query 1', ['abc' => 123], time(), $connection);
    $recorder->record($query);

    $this->assertCount(1, $recorder->getQueries());
    $this->assertSame('query 1', $recorder->getQueries()[0]['sql']);
    $this->assertNull($recorder->getQueries()[0]['bindings']);
});
