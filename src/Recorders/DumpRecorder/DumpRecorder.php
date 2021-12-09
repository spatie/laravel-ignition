<?php

namespace Spatie\LaravelIgnition\Recorders\DumpRecorder;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as BaseHtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DumpRecorder
{
    /** @var array<array<int,mixed>>  */
    protected array $dumps = [];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function start(): self
    {
        $multiDumpHandler = new MultiDumpHandler();

        $this->app->singleton(MultiDumpHandler::class, fn () => $multiDumpHandler);

        $previousHandler = VarDumper::setHandler(fn ($var) => $multiDumpHandler->dump($var));

        $previousHandler
            ? $multiDumpHandler->addHandler($previousHandler)
            : $multiDumpHandler->addHandler($this->getDefaultHandler());

        $multiDumpHandler->addHandler(fn ($var) => (new DumpHandler($this))->dump($var));

        return $this;
    }

    public function record(Data $data): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
        $file = (string)Arr::get($backtrace, '6.file');
        $lineNumber = (int)Arr::get($backtrace, '6.line');

        if (! Arr::exists($backtrace, '7.class') && (string)Arr::get($backtrace, '7.function') === 'ddd') {
            $file = (string)Arr::get($backtrace, '7.file');
            $lineNumber = (int)Arr::get($backtrace, '7.line');
        }

        $htmlDump = (new HtmlDumper())->dump($data);

        $this->dumps[] = new Dump($htmlDump, $file, $lineNumber);
    }

    public function getDumps(): array
    {
        return $this->toArray();
    }

    public function reset()
    {
        $this->dumps = [];
    }

    public function toArray(): array
    {
        $dumps = [];

        foreach ($this->dumps as $dump) {
            $dumps[] = $dump->toArray();
        }

        return $dumps;
    }

    protected function getDefaultHandler(): callable
    {
        return function ($value) {
            $data = (new VarCloner)->cloneVar($value);

            $this->getDumper()->dump($data);
        };
    }

    protected function getDumper()
    {
        if (isset($_SERVER['VAR_DUMPER_FORMAT'])) {
            if ($_SERVER['VAR_DUMPER_FORMAT'] === 'html') {
                return new BaseHtmlDumper();
            }

            return new CliDumper();
        }

        if (in_array(PHP_SAPI, ['cli', 'phpdbg']) && ! isset($_SERVER['LARAVEL_OCTANE'])) {
            return new CliDumper() ;
        }

        return new BaseHtmlDumper();
    }
}
