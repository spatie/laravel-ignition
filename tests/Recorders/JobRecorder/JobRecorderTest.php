<?php

use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelIgnition\Recorders\JobRecorder\JobRecorder;
use Spatie\LaravelIgnition\Tests\stubs\Jobs\QueueableJob;

it('can record a failed job', function () {
    $recorder = (new JobRecorder(app()));

    $recorder->record(createEvent(function () {
        dispatch(new QueueableJob([]));
    }));

    $recorded = $recorder->getJob();

    expect($recorded['name'])->toEqual('Spatie\LaravelIgnition\Tests\stubs\Jobs\QueueableJob');
    expect($recorded['connection'])->toEqual('sync');
    expect($recorded['queue'])->toEqual('sync');
    $this->assertNotEmpty($recorded['uuid']);
    $this->assertNotEmpty($recorded['data']);
    expect($recorded['data']['property'])->toEqual([]);
});

it('can record a failed job with data', function () {
    $recorder = (new JobRecorder(app()));

    $job = new QueueableJob([
        'int' => 42,
        'boolean' => true,
    ]);

    $recorder->record(createEvent(function () use ($job) {
        dispatch($job);
    }));

    $recorded = $recorder->getJob();

    $this->assertNotEmpty($recorded['data']);
    $this->assertEquals([
        'int' => 42,
        'boolean' => true,
    ], $recorded['data']['property']);
});

it('can read specific properties from a job', function () {
    $recorder = (new JobRecorder(app()));

    $date = CarbonImmutable::create(2020, 05, 16, 12, 0, 0);

    $job = new QueueableJob(
        [],
        $date,  // retryUntil
        5, // tries
        10, // maxExceptions
        120 // timeout
    );

    $recorder->record(createEvent(function () use ($date, $job) {
        dispatch($job)
            ->onQueue('default')
            ->beforeCommit()
            ->delay($date);
    }));

    $recorded = $recorder->getJob();

    expect($recorded['maxTries'])->toEqual(5);
    expect($recorded['maxExceptions'])->toEqual(10);
    expect($recorded['timeout'])->toEqual(120);
    $this->assertNotEmpty($recorded['data']);
    expect($recorded['data']['queue'])->toEqual('default');
});

it('can record a closure job', function () {
    $recorder = (new JobRecorder(app()));

    $job = function () {
        throw new Exception('Die');
    };

    $recorder->record(createEvent(function () use ($job) {
        dispatch($job);
    }));

    $recorded = $recorder->getJob();

    expect($recorded['name'])->toEqual('Closure (JobRecorderTest.php:97)');
});

it('can record a chained job', function () {
    $recorder = (new JobRecorder(app()));

    $recorder->record(createEvent(function () {
        dispatch(new QueueableJob(['level-one']))->chain([
            new QueueableJob(['level-two-a']),
            (new QueueableJob(['level-two-b']))->chain([
                (new QueueableJob(['level-three'])),
            ]),
        ]);
    }));

    $recorded = $recorder->getJob();

    expect($chained = $recorded['data']['chained'])->toHaveCount(2);

    expect($chained[0]['name'])->toEqual(QueueableJob::class);
    expect($chained[0]['data']['property'])->toEqual(['level-two-a']);
    expect($chained[1]['name'])->toEqual(QueueableJob::class);
    expect($chained[1]['data']['property'])->toEqual(['level-two-b']);

    expect($chained = $chained[1]['data']['chained'])->toHaveCount(1);

    expect($chained[0]['name'])->toEqual(QueueableJob::class);
    expect($chained[0]['data']['property'])->toEqual(['level-three']);
});

it('can restrict the recorded chained jobs depth', function () {
    $recorder = (new JobRecorder(app(), 1));

    $recorder->record(createEvent(function () {
        dispatch(new QueueableJob(['level-one']))->chain([
            new QueueableJob(['level-two-a']),
            (new QueueableJob(['level-two-b']))->chain([
                (new QueueableJob(['level-three'])),
            ]),
        ]);
    }));

    $recorded = $recorder->getJob();

    expect($chained = $recorded['data']['chained'])->toHaveCount(2);

    expect($chained[0]['name'])->toEqual(QueueableJob::class);
    expect($chained[0]['data']['property'])->toEqual(['level-two-a']);
    expect($chained[1]['name'])->toEqual(QueueableJob::class);
    expect($chained[1]['data']['property'])->toEqual(['level-two-b']);

    expect($chained = $chained[1]['data']['chained'])->toHaveCount(1);
    expect($chained)->toEqual(['Ignition stopped recording jobs after this point since the max chain depth was reached']);
});

it('can disable recording chained jobs', function () {
    $recorder = (new JobRecorder(app(), 0));

    $recorder->record(createEvent(function () {
        dispatch(new QueueableJob(['level-one']))->chain([
            new QueueableJob(['level-two-a']),
            (new QueueableJob(['level-two-b']))->chain([
                (new QueueableJob(['level-three'])),
            ]),
        ]);
    }));

    $recorded = $recorder->getJob();

    expect($chained = $recorded['data']['chained'])->toHaveCount(1);
    expect($chained)->toEqual(['Ignition stopped recording jobs after this point since the max chain depth was reached']);
});

it('can handle a job with an unserializeable payload', function () {
    $recorder = (new JobRecorder(app()));

    $payload = json_encode([
        'job' => 'Fake Job Name',
    ]);

    $event = new JobExceptionOccurred(
        'redis',
        new RedisJob(
            app(Container::class),
            app(RedisQueue::class),
            $payload,
            $payload,
            'redis',
            'default'
        ),
        new Exception()
    );

    $recorder->record($event);

    $recorded = $recorder->getJob();

    expect($recorded['name'])->toEqual('Fake Job Name');
    expect($recorded['connection'])->toEqual('redis');
    expect($recorded['queue'])->toEqual('default');
});

// Helpers
function createEvent(Closure $dispatch): JobExceptionOccurred
{
    $triggeredEvent = null;

    Event::listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) use (&$triggeredEvent) {
        $triggeredEvent = $event;
    });

    try {
        $dispatch();
    } catch (Exception $exception) {
    }

    if ($triggeredEvent === null) {
        throw new Exception("Could not create test event");
    }

    return $triggeredEvent;
}
