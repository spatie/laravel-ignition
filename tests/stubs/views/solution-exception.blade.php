<h1>This is a blade view with a solution</h1>
@php
use Spatie\LaravelIgnition\Tests\TestClasses\ExceptionWithSolution;

$exception ??= new ExceptionWithSolution;

throw $exception;
@endphp

Oops! I threw up an exception.
