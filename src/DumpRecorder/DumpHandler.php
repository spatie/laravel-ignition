<?php

namespace Spatie\LaravelIgnition\DumpRecorder;

use Symfony\Component\VarDumper\Cloner\VarCloner;

class DumpHandler
{
    protected DumpRecorder $dumpRecorder;

    public function __construct(DumpRecorder $dumpRecorder)
    {
        $this->dumpRecorder = $dumpRecorder;
    }

    public function dump($value): void
    {
        $data = (new VarCloner)->cloneVar($value);

        $this->dumpRecorder->record($data);
    }
}
