<?php

namespace Spatie\Ignition\Exceptions;

use Exception;
use Spatie\IgnitionContracts\BaseSolution;
use Spatie\IgnitionContracts\ProvidesSolution;
use Spatie\IgnitionContracts\Solution;
use Monolog\Logger;

class InvalidConfig extends Exception implements ProvidesSolution
{
    public static function invalidLogLevel(string $logLevel)
    {
        return new static("Invalid log level `{$logLevel}` specified.");
    }

    public function getSolution(): Solution
    {
        $validLogLevels = array_map(function (string $level) {
            return strtolower($level);
        }, array_keys(Logger::getLevels()));

        $validLogLevelsString = implode(',', $validLogLevels);

        return BaseSolution::create('You provided an invalid log level')
            ->setSolutionDescription("Please change the log level in your `config/logging.php` file. Valid log levels are {$validLogLevelsString}.");
    }
}
