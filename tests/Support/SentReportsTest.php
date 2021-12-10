<?php

use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Support\SentReports;
use Spatie\LaravelIgnition\Tests\TestCase;


beforeEach(function () {
    $this->sentReports = new SentReports();
});

it('can get the uuids', function () {
    expect($this->sentReports->latestUuid())->toBeNull();

    $report = getReport('first-report');
    $this->sentReports->add($report);
    expect($this->sentReports->latestUuid())->toEqual('first-report');

    $report = getReport('second-report');
    $this->sentReports->add($report);
    expect($this->sentReports->latestUuid())->toEqual('second-report');

    $this->assertEquals([
        'first-report',
        'second-report',
    ], $this->sentReports->uuids());
});

it('can get the error urls', function () {
    $report = getReport('first-report');
    $this->sentReports->add($report);

    expect($this->sentReports->latestUrl())->toEqual('https://flareapp.io/tracked-occurrence/first-report');

    $report = getReport('second-report');
    $this->sentReports->add($report);
    expect($this->sentReports->latestUrl())->toEqual('https://flareapp.io/tracked-occurrence/second-report');

    $this->assertEquals([
        'https://flareapp.io/tracked-occurrence/first-report',
        'https://flareapp.io/tracked-occurrence/second-report',
    ], $this->sentReports->urls());
});

it('can be cleared', function () {
    $report = getReport('first-report');
    $this->sentReports->add($report);
    expect($this->sentReports->all())->toHaveCount(1);

    $this->sentReports->clear();
    expect($this->sentReports->all())->toHaveCount(0);
});

// Helpers
function getReport(string $fakeUuid = 'fake-uuid'): Report
{
    Report::$fakeTrackingUuid = $fakeUuid;

    return Flare::report(new Exception());
}
