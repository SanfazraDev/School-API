<?php

namespace App\Http\Requests\AcademicYear;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
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
        // Dapatkan academicYear instance dari route
        $academicYear = $this->route('academic_year');

        $startDate = $this->input('start_date', $academicYear->start_date);

        return [
            'year_start' => ['sometimes', 'required', 'integer', 'min:2000'],
            'year_end' => ['sometimes', 'required', 'integer', 'min:2000', 'gt:' . $this->input('year_start', $academicYear->year_start)],
            'semester' => [
                'sometimes',
                'required',
                'integer',
                'in:1,2',
                Rule::unique('academic_years')->where(function ($query) use ($academicYear) {
                    $yearStart = $this->input('year_start', $academicYear->year_start);
                    $yearEnd = $this->input('year_end', $academicYear->year_end);
                    
                    return $query->where('year_start', $yearStart)
                                 ->where('year_end', $yearEnd);
                })->ignore($academicYear->id),
            ],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:' . $startDate],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year_start.min' => 'The year start must be at least 2000.',
            'year_end.min' => 'The year end must be at least 2000.',
            'year_end.gt' => 'The year end must be greater than year start.',
            'semester.in' => 'The semester must be 1 or 2.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to start date.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::validationError($validator->errors())
        );
    }
}
