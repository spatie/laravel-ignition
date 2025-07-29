<?php

namespace Spatie\LaravelIgnition\Exceptions;

use ErrorException;
use Spatie\FlareClient\Contracts\ProvidesFlareContext;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\HtmlDumper;

class ViewException extends ErrorException implements ProvidesFlareContext
{
    /** @var array<string, mixed> */
    protected array $viewData = [];

    protected bool $dataAsHtml = false;

    protected string $view = '';

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function setViewData(array $data): void
    {
        $this->viewData = $data;
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function setView(string $path): void
    {
        $this->view = $path;
    }

    public function setDataAsHtml(bool $asHtml = true): void
    {
        $this->dataAsHtml = $asHtml;
    }

    protected function dumpViewData(mixed $variable): string
    {
        return (new HtmlDumper())->dumpVariable($variable);
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        $context = [
            'view' => [
                'view' => $this->view,
            ],
        ];

        if ($this->dataAsHtml) {
            $context['view']['data'] = array_map([$this, 'dumpViewData'], $this->viewData);
        } else {
            $context['view']['data'] = $this->viewData;
        }

        return $context;
    }
}
