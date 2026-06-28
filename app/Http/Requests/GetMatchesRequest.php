<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetMatchesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => 'integer|min:2000|max:2100',
            'team' => 'string|max:100|nullable',
            'round' => 'string|max:100|nullable',
            'status' => 'string|in:scheduled,in_progress,completed|nullable',
        ];
    }
}
