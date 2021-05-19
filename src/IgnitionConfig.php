<?php

namespace Spatie\Ignition;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class IgnitionConfig implements Arrayable
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $this->mergeWithDefaultConfig($options);
    }

    public function editor(): ?string
    {
        return Arr::get($this->options, 'editor');
    }

    public function remoteSitesPath(): ?string
    {
        return Arr::get($this->options, 'remote_sites_path');
    }

    public function localSitesPath(): ?string
    {
        return Arr::get($this->options, 'local_sites_path');
    }

    public function theme(): ?string
    {
        return Arr::get($this->options, 'theme');
    }

    public function shareButtonEnabled(): bool
    {
        if (! app()->isBooted()) {
            return false;
        }

        return Arr::get($this->options, 'enable_share_button', true);
    }

    public function runnableSolutionsEnabled(): bool
    {
        $enabled = Arr::get($this->options, 'enable_runnable_solutions', null);

        if ($enabled === null) {
            $enabled = config('app.debug');
        }

        return $enabled ?? false;
    }

    public function toArray(): array
    {
        return [
            'editor' => $this->editor(),
            'remoteSitesPath' => $this->remoteSitesPath(),
            'localSitesPath' => $this->localSitesPath(),
            'theme' => $this->theme(),
            'enableShareButton' => $this->shareButtonEnabled(),
            'enableRunnableSolutions' => $this->runnableSolutionsEnabled(),
            'directorySeparator' => DIRECTORY_SEPARATOR,
        ];
    }

    protected function mergeWithDefaultConfig(array $options = []): array
    {
        return array_merge(config('ignition') ?: include __DIR__.'/../config/ignition.php', $options);
    }
}
