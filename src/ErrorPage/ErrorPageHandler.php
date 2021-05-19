<?php

namespace Spatie\Ignition\ErrorPage;

use Illuminate\Foundation\Application;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Report;
use Spatie\Ignition\IgnitionConfig;
use Spatie\IgnitionContracts\SolutionProviderRepository;
use Throwable;

class ErrorPageHandler
{
    protected IgnitionConfig $ignitionConfig;

    protected Flare $flareClient;

    protected Renderer $renderer;

    protected SolutionProviderRepository $solutionProviderRepository;

    public function __construct(
        Application $app,
        IgnitionConfig $ignitionConfig,
        Renderer $renderer,
        SolutionProviderRepository $solutionProviderRepository
    ) {
        $this->flareClient = $app->make(Flare::class);
        $this->ignitionConfig = $ignitionConfig;
        $this->renderer = $renderer;
        $this->solutionProviderRepository = $solutionProviderRepository;
    }

    public function handle(Throwable $throwable, $defaultTab = null, $defaultTabProps = []) : void
    {
        $report = $this->flareClient->createReport($throwable);

        $solutions = $this->solutionProviderRepository->getSolutionsForThrowable($throwable);

        $viewModel = new ErrorPageViewModel(
            $throwable,
            $this->ignitionConfig,
            $report,
            $solutions
        );

        $viewModel->defaultTab($defaultTab, $defaultTabProps);

        $this->renderException($viewModel);
    }

    public function handleReport(Report $report, $defaultTab = null, $defaultTabProps = []): void
    {
        $viewModel = new ErrorPageViewModel(
            $report->getThrowable(),
            $this->ignitionConfig,
            $report,
            [],
        );

        $viewModel->defaultTab($defaultTab, $defaultTabProps);

        $this->renderException($viewModel);
    }

    protected function renderException(ErrorPageViewModel $exceptionViewModel): void
    {
        echo $this->renderer->render(
            'errorPage',
            $exceptionViewModel->toArray(),
        );
    }
}
