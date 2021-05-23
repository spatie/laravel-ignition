<?php

namespace Spatie\LaravelIgnition\ErrorPage;

use Illuminate\Foundation\Application;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Report;
use Spatie\Ignition\Ignition;
use Spatie\Ignition\IgnitionConfig;
use Spatie\Ignition\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\Ignition\SolutionProviders\UndefinedPropertySolutionProvider;
use Spatie\IgnitionContracts\SolutionProviderRepository;
use Spatie\LaravelIgnition\SolutionProviders\DefaultDbNameSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\IncorrectValetDbCredentialsSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\InvalidRouteActionSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingColumnSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingImportSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingLivewireComponentSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\RunningLaravelDuskInProductionProvider;
use Spatie\LaravelIgnition\SolutionProviders\TableNotFoundSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\UnknownValidationSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\ViewNotFoundSolutionProvider;
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
        Ignition::make()
            //->addSolutionsProviders($this->getSolutions())
            ->useFlare(app(Flare::class))
            ->renderException($throwable);


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

    protected function getSolutions(): array
    {
        return [
            IncorrectValetDbCredentialsSolutionProvider::class,
            MissingAppKeySolutionProvider::class,
            DefaultDbNameSolutionProvider::class,
            BadMethodCallSolutionProvider::class,
            TableNotFoundSolutionProvider::class,
            MissingImportSolutionProvider::class,
            InvalidRouteActionSolutionProvider::class,
            ViewNotFoundSolutionProvider::class,
            MergeConflictSolutionProvider::class,
            RunningLaravelDuskInProductionProvider::class,
            MissingColumnSolutionProvider::class,
            UnknownValidationSolutionProvider::class,
            UndefinedPropertySolutionProvider::class,
            MissingMixManifestSolutionProvider::class,
            MissingLivewireComponentSolutionProvider::class,
        ];
    }
}
