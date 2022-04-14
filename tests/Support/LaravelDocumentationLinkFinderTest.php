<?php

use Illuminate\Auth\AuthenticationException;
use Spatie\LaravelIgnition\Support\LaravelDocumentationLinkFinder;
use Spatie\LaravelIgnition\Support\LaravelVersion;

beforeEach(function () {
    $this->finder = new LaravelDocumentationLinkFinder();
});

it('can find a link for a laravel exception', function () {
    $link = $this->finder->findLinkForThrowable(new AuthenticationException());

    $majorVersion = LaravelVersion::major();

    expect($link)->toEqual("https://laravel.com/docs/{$majorVersion}.x/authentication");
});
