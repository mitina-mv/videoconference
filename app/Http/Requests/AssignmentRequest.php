<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;
use Illuminate\Support\Str;

class AssignmentRequest extends Request
{
    public function updateRules() : array
    {
        return [
            'id' => ['required', 'exists:assignments,id', 'bail'],
            'date' => ['required', 'string', 'max:255'],
            'test_id' => ['required', 'exists:tests,id', 'bail'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
            'vc_id' => ['nullable', 'exists:videoconferences,id', 'bail'],
        ];
    }

    public function storeRules() : array
    {
        return [
            'date' => ['required', 'string', 'max:255'],
            'test_id' => ['required', 'exists:tests,id', 'bail'],
            'vc_id' => ['nullable', 'exists:videoconferences,id', 'bail'],
            'user_id' => ['required', 'exists:users,id', 'bail'],
            'moodle_code' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function prepareForValidation()
    {
        $VCname = request()->vc_name;
        $moodle_code = request()->moodle_code;

        if($VCname && !$moodle_code) {
            $moodle_code = Str::slug($VCname, '-');
        }
        return $this->merge([
            'id' => request()->assignment,
            'user_id' => auth()->id(),
            'moodle_code' => $moodle_code,
        ]);
    }
}
