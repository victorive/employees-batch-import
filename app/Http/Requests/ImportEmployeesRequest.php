<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CsvDelimiter;

class ImportEmployeesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv',
            ],
            'delimiter' => [
                'nullable',
                'string',
                Rule::in(CsvDelimiter::getAllAsArray()),
            ]
        ];
    }

    public function messages()
    {
        return [
            'delimiter.in' => 'The delimiter must be one of: ' . implode(', ', CsvDelimiter::getAllAsArray()),
        ];
    }
}
