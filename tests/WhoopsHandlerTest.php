<?php

namespace Spatie\LaravelIgnition\Tests;

use Illuminate\Support\Facades\Route;

class WhoopsHandlerTest extends TestCase
{
    /** @test */
    public function it_uses_a_custom_whoops_handler()
    {
        config()->set('app.debug', true);

        Route::get('exception', function () {
            whoops();
        });

        $result = $this->get('/exception');

        $this->assertTrue(is_string($result->getContent()));
    }
}
