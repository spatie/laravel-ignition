<?php

namespace Spatie\LaravelIgnition\Tests;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\Request;
use Spatie\FlareClient\Glows\Glow;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Facades\Flare;
use Spatie\LaravelIgnition\IgnitionServiceProvider;
use Spatie\LaravelIgnition\Tests\TestClasses\FakeTime;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use MakesHttpRequests;

    protected function setUp(): void
    {
        ray()->newScreen($this->getName());

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        config()->set('flare.key', 'dummy-key');

        return [IgnitionServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Flare' => Flare::class,
        ];
    }

    public function useTime(string $dateTime, string $format = 'Y-m-d H:i:s')
    {
        $fakeTime = new FakeTime($dateTime, $format);

        Report::useTime($fakeTime);
        Glow::useTime($fakeTime);
    }

    public function createRequest($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): Request
    {
        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));

        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        return Request::createFromBase($symfonyRequest);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        if (in_array($this->getName(), [
            'it can update the config',
            'it can execute solutions on a local environment with debugging enabled',
            'it wont execute solutions on a production environment',
            'it wont execute solutions when debugging is disabled',
            'it wont execute solutions for a non local ip',

        ])) {
            // Routes will not register in a console environment.
            $_ENV['APP_RUNNING_IN_CONSOLE'] = false;
        }
    }
}
