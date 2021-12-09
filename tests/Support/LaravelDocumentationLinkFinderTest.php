<?php

namespace Spatie\LaravelIgnition\Tests\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Broadcasting\BroadcastException;
use Spatie\LaravelIgnition\Support\LaravelDocumentationLinkFinder;
use Spatie\LaravelIgnition\Tests\TestCase;

class LaravelDocumentationLinkFinderTest extends TestCase
{
    protected LaravelDocumentationLinkFinder $finder;

    public function setUp(): void
    {
        parent::setUp();

        $this->finder = new LaravelDocumentationLinkFinder();
    }

    /** @test */
    public function it_can_find_a_link_for_a_laravel_exception()
    {
        $link = $this->finder->findLinkForThrowable(new AuthenticationException());

        $this->assertEquals('https://laravel.com/docs/8.x/authentication', $link);
    }
}
