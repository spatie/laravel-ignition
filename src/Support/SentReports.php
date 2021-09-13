<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Arr;
use Spatie\FlareClient\Report;

class SentReports
{
    /** @var array<int, Report> */
    protected array $reports = [];

    public function add(Report $report): self
    {
        $this->reports[] = $report;

        return $this;
    }

    public function all(): array
    {
        return $this->reports;
    }

    public function uuids(): array
    {
        return array_map(fn (Report $report) => $report->trackingUuid(), $this->reports);
    }

    public function urls(): array
    {
        return array_map(function (string $trackingUuid) {
            return "https://flareapp.io/tracked-occurrence/{$trackingUuid}";
        }, $this->uuids());
    }

    public function latestUuid(): ?string
    {
        return Arr::last($this->reports)?->trackingUuid();
    }

    public function latestUrl(): ?string
    {
        return Arr::last($this->urls());
    }

    public function clear()
    {
        $this->reports = [];
    }
}
