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
            'settings' => ['sometimes', 'array'],
            'settings.count_questions' => ['nullable', 'integer', 'between:1,100'],
            'settings.is_random' => ['nullable', 'boolean'],
            'settings.fixed_questions' => ['nullable', 'boolean'],
            'settings.question_ids' => ['nullable', 'array'],
            
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1024'],
            'theme_id' => ['required', 'exists:themes,id', 'bail'],
            'settings' => ['sometimes', 'array'],
            'settings.count_questions' => ['nullable', 'integer', 'between:1,100'],
            'settings.is_random' => ['nullable', 'boolean'],
            'settings.question_ids' => ['nullable', 'array'],
            'settings.fixed_questions' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {            

        return $this->merge([
            'id' => request()->test,
            'user_id' => auth()->id(),
        ]);
    }
}
