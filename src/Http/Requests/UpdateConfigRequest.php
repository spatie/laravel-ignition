<?php

namespace Spatie\LaravelIgnition\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Ignition\Contracts\RunnableSolution;
use Spatie\Ignition\Contracts\Solution;
use Spatie\Ignition\Contracts\SolutionProviderRepository;

class UpdateConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'theme' => Rule::in(['light', 'dark']),
        ];
    }
}
