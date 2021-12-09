<?php

namespace Spatie\LaravelIgnition\Support\Composer;

class FakeComposer
{
    /** @return array<string, string> */
    public function getClassMap(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public function getPrefixes(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public function getPrefixesPsr4(): array
    {
        return [];
    }
}
