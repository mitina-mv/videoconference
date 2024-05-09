<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Orion\Http\Requests\Request;
use Illuminate\Validation\Rule;

class QuestionRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:questions,id', 'bail'],
            'text' => ['required', 'string', 'max:512'],
            'is_private' => ['sometimes', 'boolean'],
            'mark' => ['required', 'numeric', 'min:1', 'max:100'],
            'theme_id' => ['required', 'exists:themes,id', 'bail'],
            'type' => ['required', 'in:single,multiple,text'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'text' => ['required', 'string', 'max:512'],
            'is_private' => ['sometimes', 'boolean'],
            'mark' => ['required', 'numeric', 'min:1', 'max:100'],
            'type' => ['required', 'in:single,multiple,text'],
            'theme_id' => ['required', 'exists:themes,id', 'bail'],
        ];
    }

    public function prepareForValidation()
    {
        $userId = request()->user_id;
        if(is_null($userId)) {
            $userId = request()->user()->id;
        }
            
        return $this->merge([
            'user_id' => $userId,
            'id' => request()->question
        ]);
    }
}
