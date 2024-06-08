<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class VideoconferenceRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:videoconferences,id', 'bail'],
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
            'settings' => ['sometimes', 'array'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
            'settings' => ['sometimes', 'array'],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => request()->videoconference,
            'user_id' => auth()->id(),
        ]);
    }
}
