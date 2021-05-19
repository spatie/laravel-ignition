<?php

namespace Spatie\Ignition\Tabs;

use Illuminate\Support\Str;
use JsonSerializable;
use Spatie\FlareClient\Flare;
use Throwable;

abstract class Tab implements JsonSerializable
{
    public array $scripts = [];

    public array $styles = [];

    protected Flare $flare;

    protected Throwable $throwable;

    public function __construct()
    {
        $this->registerAssets();
    }

    public function name(): string
    {
        return Str::studly(class_basename(get_called_class()));
    }

    public function component(): string
    {
        return Str::snake(class_basename(get_called_class()), '-');
    }

    public function beforeRenderingErrorPage(Flare $flare, Throwable $throwable): void
    {
        $this->flare = $flare;

        $this->throwable = $throwable;
    }

    public function script(string $name, string $path): self
    {
        $this->scripts[$name] = $path;

        return $this;
    }

    public function style(string $name, string $path): self
    {
        $this->styles[$name] = $path;

        return $this;
    }

    abstract protected function registerAssets();

    public function meta(): array
    {
        return [];
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->name(),
            'component' => $this->component(),
            'props' => [
                'meta' => $this->meta(),
            ],
        ];
    }
}
