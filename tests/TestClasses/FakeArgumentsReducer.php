<?php

namespace Spatie\LaravelIgnition\Tests\TestClasses;

use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\Reducers\ArgumentReducer;

class FakeArgumentsReducer implements ArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        return new ReducedArgument('FAKE', gettype($argument));
    }
}
