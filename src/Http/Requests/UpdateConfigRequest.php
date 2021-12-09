<?php

namespace Spatie\LaravelIgnition\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'theme' => Rule::in(['light', 'dark']),
        ];
    }
}
