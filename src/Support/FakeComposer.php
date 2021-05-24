<?php

namespace Spatie\LaravelIgnition\Support;

class FakeComposer
{
    public function getClassMap(): array
    {
        return [];
    }

    public function getPrefixes(): array
    {
        return [];
    }

    public function getPrefixesPsr4(): array
    {
        return [];
    }
}
