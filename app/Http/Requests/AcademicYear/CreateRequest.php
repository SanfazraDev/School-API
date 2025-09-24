<?php

namespace App\Http\Requests\AcademicYear;

use App\Helpers\ResponseHelper;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateRequest extends FormRequest
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
            'year_start' => ['required', 'integer', 'min:2000'],
            'year_end' => ['required', 'integer', 'min:2000', 'gt:year_start'],
            'semester' => [
                'required', 
                'integer', 
                'in:1,2',
                Rule::unique('academic_years')
                    ->where(fn ($query) => $query
                        ->where('year_start', $this->year_start)
                        ->where('year_end', $this->year_end)
                    )
            ],
            'is_active' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year_start.min' => 'The year start must be at least 2000.',
            'year_end.min' => 'The year end must be at least 2000.',
            'year_end.gt' => 'The year end must be greater than year start.',
            'name.max' => 'The name must not be greater than 255 characters.',
            'semester.in' => 'The semester must be odd or even.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to start date.',
            'description.max' => 'The description must not be greater than 255 characters.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::validationError($validator->errors()),
        );
    }
}
