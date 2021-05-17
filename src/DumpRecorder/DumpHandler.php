<?php

namespace Spatie\Ignition\DumpRecorder;

use Symfony\Component\VarDumper\Cloner\VarCloner;

class DumpHandler
{
    /** @var \Spatie\Ignition\DumpRecorder\DumpRecorder */
    protected $dumpRecorder;

    public function __construct(DumpRecorder $dumpRecorder)
    {
        $this->dumpRecorder = $dumpRecorder;
    }

    public function dump($value)
    {
        $data = (new VarCloner)->cloneVar($value);

        $this->dumpRecorder->record($data);
    }
}
