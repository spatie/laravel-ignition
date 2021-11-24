<?php

namespace Spatie\LaravelIgnition\ContextProviders;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Livewire\LivewireManager;

class LaravelLivewireRequestContextProvider extends LaravelRequestContextProvider
{
    public function __construct(
        Request $request,
        protected LivewireManager $livewireManager
    ) {
        parent::__construct($request);
    }

    public function getRequest(): array
    {
        $properties = parent::getRequest();

        $properties['method'] = $this->livewireManager->originalMethod();
        $properties['url'] = $this->livewireManager->originalUrl();

        return $properties;
    }

    public function toArray(): array
    {
        $properties = parent::toArray();

        $properties['livewire'] = $this->getLiveWireInformation();

        return $properties;
    }

    protected function getLiveWireInformation(): array
    {
        $componentId = $this->request->input('fingerprint.id');
        $componentAlias = $this->request->input('fingerprint.name');

        if ($componentAlias === null) {
            return [];
        }

        try {
            $componentClass = $this->livewireManager->getClass($componentAlias);
        } catch (Exception $e) {
            $componentClass = null;
        }

        return [
            'component_class' => $componentClass,
            'component_alias' => $componentAlias,
            'component_id' => $componentId,
            'data' => $this->resolveData(),
            'updates' => $this->resolveUpdates(),
        ];
    }

    protected function resolveData(): array
    {
        $data = $this->request->input('serverMemo.data') ?? [];

        $dataMeta = $this->request->input('serverMemo.dataMeta') ?? [];

        foreach ($dataMeta['modelCollections'] ?? [] as $key => $value) {
            $data[$key] = array_merge($data[$key] ?? [], $value);
        }

        foreach ($dataMeta['models'] ?? [] as $key => $value) {
            $data[$key] = array_merge($data[$key] ?? [], $value);
        }

        return $data;
    }

    protected function resolveUpdates(): array
    {
        $updates = $this->request->input('updates') ?? [];

        return array_map(function (array $update) {
            $update['payload'] = Arr::except($update['payload'] ?? [], ['id']);

            return $update;
        }, $updates);
    }
}
