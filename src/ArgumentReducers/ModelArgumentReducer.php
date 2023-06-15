<?php

namespace Spatie\LaravelIgnition\ArgumentReducers;

use Illuminate\Database\Eloquent\Model;
use Spatie\FlareClient\Arguments\ReducedArgument\ReducedArgument;
use Spatie\FlareClient\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\FlareClient\Arguments\ReducedArgument\UnReducedArgument;
use Spatie\FlareClient\Arguments\Reducers\ArgumentReducer;

class ModelArgumentReducer implements ArgumentReducer
{
    public function execute(mixed $argument): ReducedArgumentContract
    {
        if (! $argument instanceof Model) {
            return UnReducedArgument::create();
        }

        return new ReducedArgument(
            "{$argument->getKeyName()}|{$argument->getKey()}",
            get_class($argument)
        );
    }
}
