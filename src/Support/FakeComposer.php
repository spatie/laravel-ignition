<?php

namespace Spatie\Ignition\Support;

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
