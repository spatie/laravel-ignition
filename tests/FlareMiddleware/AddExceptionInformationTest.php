<?php

use Illuminate\Database\QueryException;
use Spatie\LaravelIgnition\Facades\Flare;

it('will add query information with a query exception', function () {
    $sql = 'select * from users where emai = "ruben@spatie.be"';

    $report = Flare::createReport(new QueryException(
        '' . $sql . '',
        [],
        new Exception()
    ));

    $context = $report->toArray()['context'];

    $this->assertArrayHasKey('exception', $context);
    expect($context['exception']['raw_sql'])->toBe($sql);
});

it('wont add query information without a query exception', function () {
    $report = Flare::createReport(new Exception());

    $context = $report->toArray()['context'];

    $this->assertArrayNotHasKey('exception', $context);
});

it('will add user context when provided on a custom exception', function () {
    $report = Flare::createReport(new class extends Exception {
        public function context()
        {
            return [
                'hello' => 'world',
            ];
        }
    });

    $context = $report->toArray()['context'];

    expect($context['context']['hello'])->toBe('world');
});

it('will only add arrays as user provided context', function () {
    $report = Flare::createReport(new class extends Exception {
        public function context()
        {
            return (object) [
                'hello' => 'world',
            ];
        }
    });

    $context = $report->toArray()['context'];

    expect($context)->not()->toHaveKey('context');
});
