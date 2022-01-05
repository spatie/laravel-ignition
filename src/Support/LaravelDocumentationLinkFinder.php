<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Throwable;

class LaravelDocumentationLinkFinder
{
    public function findLinkForThrowable(Throwable $throwable): ?string
    {
        if ($throwable instanceof ViewException) {
            $throwable = $throwable->getPrevious();
        }

        if (! str_starts_with($throwable::class, 'Illuminate')) {
            return null;
        }

        $type = Str::between($throwable::class, 'Illuminate\\', '\\');

        $majorVersion = substr(app()->version(), 0, 1);

        return match ($type) {
            'Auth' => "https://laravel.com/docs/{$majorVersion}.x/authentication",
            'Broadcasting' => "https://laravel.com/docs/{$majorVersion}.x/broadcasting",
            'Container' => "https://laravel.com/docs/{$majorVersion}.x/container",
            'Database' => "https://laravel.com/docs/{$majorVersion}.x/eloquent",
            'Pagination' => "https://laravel.com/docs/{$majorVersion}.x/pagination",
            'Queue' => "https://laravel.com/docs/{$majorVersion}.x/queues",
            'Routing' => "https://laravel.com/docs/{$majorVersion}.x/routing",
            'Session' => "https://laravel.com/docs/{$majorVersion}.x/session",
            'Validation' => "https://laravel.com/docs/{$majorVersion}.x/validation",
            'View' => "https://laravel.com/docs/{$majorVersion}.x/views",
            default => 'https://laravel.com/docs/{$majorVersion}.x/',
        };
    }
}
