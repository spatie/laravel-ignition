<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

uses(TestCase::class);

beforeEach(function () {
    config()->set('app.debug', true);

    Route::get('will-fail', function () {
        throw new Exception('My exception');
    });
});

test('when requesting html it will respond with html', function () {
    $response = $this
        ->get('will-fail')
        ->baseResponse;

    $this->assertStringStartsWith('text/html', $response->headers->get('Content-Type'));
    $this->assertTrue(Str::contains($response->getContent(), 'html'));
});

test('when requesting json it will respond with json', function () {
    /** @var \Illuminate\Http\Response $response */
    $response = $this->getJson('will-fail');

    $this->assertStringStartsWith('application/json', $response->headers->get('Content-Type'));
    $this->assertEquals('My exception', json_decode($response->getContent(), true)['message']);
});
