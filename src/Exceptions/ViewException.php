<?php

namespace Spatie\Ignition\Exceptions;

use ErrorException;
use Spatie\FlareClient\Contracts\ProvidesFlareContext;
use Spatie\Ignition\DumpRecorder\HtmlDumper;

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

        if (config('flare.reporting.report_view_data')) {
            $context['view']['data'] = array_map([$this, 'dumpViewData'], $this->viewData);
        }

        return $context;
    }
}
