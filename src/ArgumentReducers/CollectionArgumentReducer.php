<?php

namespace Spatie\LaravelIgnition\ArgumentReducers;

use Illuminate\Support\Collection;
use Spatie\FlareClient\Arguments\ReducedArgument\ReducedArgument;
use Spatie\FlareClient\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\FlareClient\Arguments\ReducedArgument\UnReducedArgument;
use Spatie\FlareClient\Arguments\Reducers\ArgumentReducer;
use Spatie\FlareClient\Arguments\Reducers\ArrayArgumentReducer;

class CollectionArgumentReducer extends ArrayArgumentReducer
{
    public function execute(mixed $argument): ReducedArgument|UnReducedArgument
    {
        if (! $argument instanceof Collection) {
            return UnReducedArgument::create();
        }

        return $this->reduceArgument($argument->toArray(), get_class($argument));
    }
}
