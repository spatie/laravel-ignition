<?php

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Spatie\Ignition\Contracts\SolutionProviderRepository;
use Spatie\LaravelIgnition\Exceptions\CannotExecuteSolutionForNonLocalEnvironment;
use Spatie\LaravelIgnition\Exceptions\CannotExecuteSolutionForNonLocalIp;
use Spatie\LaravelIgnition\Http\Requests\ExecuteSolutionRequest;

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
