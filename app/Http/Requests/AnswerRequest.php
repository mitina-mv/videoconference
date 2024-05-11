<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Orion\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AnswerRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:answers,id', 'bail'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
            'question_id' => ['required', 'exists:questions,id', 'bail'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
            'question_id' => ['required', 'exists:questions,id', 'bail'],
        ];
    }

    public function prepareForValidation()
    {            
        return $this->merge([
            'id' => request()->answer
        ]);
    }
}
