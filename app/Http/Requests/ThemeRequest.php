<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Orion\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ThemeRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:themes,id', 'bail'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('themes')->ignore(request()->id)],
            'discipline_id' => ['required', 'exists:disciplines,id', 'bail'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'discipline_id' => ['required', 'exists:disciplines,id', 'bail'],
            'code' => ['nullable', 'string', 'max:255', 'unique:disciplines'],
        ];
    }

    public function prepareForValidation()
    {
        $code = request()->code;
        if(is_null($code)) {
            $code = Str::slug(request()->name);
        }
            
        return $this->merge(['code' => $code]);
    }
}
