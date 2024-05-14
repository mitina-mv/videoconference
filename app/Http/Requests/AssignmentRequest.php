<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class AssignmentRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:assignments,id', 'bail'],
            'date' => ['required', 'string', 'max:255'],
            'test_id' => ['required', 'exists:tests,id', 'bail'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'date' => ['required', 'string', 'max:255'],
            'test_id' => ['required', 'exists:tests,id', 'bail'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => request()->assignment,
            'user_id' => auth()->id(),
        ]);
    }
}
