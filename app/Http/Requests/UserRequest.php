<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Str;
use Orion\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UserRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore(request()->id), 'bail'],
            'role_id' => ['required', 'exists:roles,id', 'bail'],
            'id' => ['required', 'exists:users,id', 'bail'],
            'studgroup_id' => ['nullable', 'exists:studgroups,id', 'bail'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id', 'bail'],
            'studgroup_id' => ['nullable', 'exists:studgroups,id', 'bail'],
        ];
    }

    public function prepareForValidation()
    {
        if(request()->user)
            return $this->merge(['id' => request()->user]);
        else
            return $this->merge([]);
    }
}
