<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Orion\Http\Requests\Request;
use Illuminate\Validation\Rule;

class StudgroupRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:studgroups,id', 'bail'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('studgroups')->ignore(request()->id)],
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:studgroups'],
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
