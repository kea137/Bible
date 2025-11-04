<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'readable' => 'required|string',
            'language' => 'required|string',
            'no_paragraphs' => 'required|integer|min:1',
            'paragraphs.*.text' => 'required|string',
            'new_series_title' => 'nullable|string|max:255',
            'new_series_description' => 'nullable|string|max:1000',
            'series_id' => 'nullable|integer|exists:lesson_series,id',
            'episode_number' => 'nullable|integer|min:1',
        ];
    }
}
