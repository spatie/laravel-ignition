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

    public function allUuids(): array
    {
        return array_map(fn (Report $report) => $report->uuid(), $this->reports);
    }

    public function latestUuid(): ?string
    {
        if (! $lastReport = Arr::last($this->reports)) {
            return null;
        }

        return $lastReport->uuid();
    }

    public function clear()
    {
        $this->reports = [];
    }
}
