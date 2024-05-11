<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class TestRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:tests,id', 'bail'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1024'],
            'theme_id' => ['required', 'exists:themes,id', 'bail'],
            'settings' => ['sometimes', 'json'],
            'settings.count_questions' => ['nullable', 'integer', 'between:1,100'],
            'settings.is_random' => ['nullable', 'boolean'],
            'settings.fixed_questions' => ['nullable', 'boolean'],
            
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1024'],
            'theme_id' => ['required', 'exists:themes,id', 'bail'],
            'settings' => ['sometimes', 'json'],
            'settings.count_questions' => ['nullable', 'integer', 'between:1,100'],
            'settings.is_random' => ['nullable', 'boolean'],
            'settings.fixed_questions' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {            
        $settings = $this->input('settings', []);

        return $this->merge([
            'id' => request()->test,
            'user_id' => auth()->id(),
            // 'settings' => json_decode($settings, true)
        ]);
    }
}
