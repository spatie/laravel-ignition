<?php

namespace Spatie\LaravelIgnition\Exceptions;

use ErrorException;
use Spatie\FlareClient\Contracts\ProvidesFlareContext;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\HtmlDumper;

class ViewException extends ErrorException implements ProvidesFlareContext
{
    protected array $viewData = [];

    protected string $view = '';

    public function setViewData(array $data)
    {
        $this->viewData = $data;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function setView(string $path)
    {
        $this->view = $path;
    }

    protected function dumpViewData($variable): string
    {
        return (new HtmlDumper())->dumpVariable($variable);
    }

    public function context(): array
    {
        $context = [
            'view' => [
                'view' => $this->view,
            ],
        ];

        $context['view']['data'] = array_map([$this, 'dumpViewData'], $this->viewData);

        return $context;
    }
}
