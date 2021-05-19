<?php

namespace Spatie\Ignition\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Spatie\Ignition\Exceptions\CannotExecuteSolutionForNonLocalEnvironment;
use Spatie\Ignition\Exceptions\CannotExecuteSolutionForNonLocalIp;
use Spatie\Ignition\Http\Requests\ExecuteSolutionRequest;
use Spatie\IgnitionContracts\SolutionProviderRepository;

class ExecuteSolutionController
{
    use ValidatesRequests;

    public function __invoke(
        ExecuteSolutionRequest $request,
        SolutionProviderRepository $solutionProviderRepository
    ) {
        $this
            ->ensureLocalEnvironment()
            ->ensureLocalRequest();

        $solution = $request->getRunnableSolution();

        $solution->run($request->get('parameters', []));

        return response()->noContent();
    }

    public function ensureLocalEnvironment(): self
    {
        if (! app()->environment('local')) {
            throw CannotExecuteSolutionForNonLocalEnvironment::make();
        }

        return $this;
    }

    public function ensureLocalRequest(): self
    {
        $ipIsPublic = filter_var(
            request()->ip(),
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($ipIsPublic) {
            throw CannotExecuteSolutionForNonLocalIp::make();

        }

        return $this;
    }
}
