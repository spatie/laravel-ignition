<?php

use Mockery\MockInterface;
use Spatie\FlareClient\Flare;

it('has a ddd function', function () {
    expect(function_exists('ddd'))->toBeTrue();
});

it('has a flare function', function () {
    expect(function_exists('flare'))->toBeTrue();
});

it('can execute the flare helper when a flare key is present', function () {
    config()->set('flare.key', 'some-key');

    $this->mock(Flare::class, function (MockInterface $mock) {
        $mock
            ->shouldReceive('context')->once()->with('testing', true)
            ->shouldReceive('report')->once()->with(new Exception('This is an exception'));
    });

    flare(new Exception('This is an exception'), [
        'testing' => true,
    ]);
});

it('will ignore the flare helper when a flare key is not present', function () {

    config()->set('flare.key', null);

    app()->bind(Flare::class, function () {
        return throw new Exception('This should not be called');
    });

    flare(new Exception('This is an exception'));

})->throwsNoExceptions();
