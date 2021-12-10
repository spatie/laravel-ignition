<?php

use Exception;
use Flare;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Support\SentReports;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->sentReports = new SentReports();
});

it('can get the uuids', function () {
    $this->assertNull($this->sentReports->latestUuid());

    $report = getReport('first-report');
    $this->sentReports->add($report);
    $this->assertEquals('first-report', $this->sentReports->latestUuid());

    $report = getReport('second-report');
    $this->sentReports->add($report);
    $this->assertEquals('second-report', $this->sentReports->latestUuid());

    $this->assertEquals([
        'first-report',
        'second-report',
    ], $this->sentReports->uuids());
});

it('can get the error urls', function () {
    $report = getReport('first-report');
    $this->sentReports->add($report);

    $this->assertEquals('https://flareapp.io/tracked-occurrence/first-report', $this->sentReports->latestUrl());

    $report = getReport('second-report');
    $this->sentReports->add($report);
    $this->assertEquals('https://flareapp.io/tracked-occurrence/second-report', $this->sentReports->latestUrl());

    $this->assertEquals([
        'https://flareapp.io/tracked-occurrence/first-report',
        'https://flareapp.io/tracked-occurrence/second-report',
    ], $this->sentReports->urls());
});

it('can be cleared', function () {
    $report = getReport('first-report');
    $this->sentReports->add($report);
    $this->assertCount(1, $this->sentReports->all());

    $this->sentReports->clear();
    $this->assertCount(0, $this->sentReports->all());
});

// Helpers
function getReport(string $fakeUuid = 'fake-uuid'): Report
{
    Report::$fakeTrackingUuid = $fakeUuid;

    return Flare::report(new Exception());
}
