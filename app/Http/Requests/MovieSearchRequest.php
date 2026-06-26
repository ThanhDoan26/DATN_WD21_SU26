<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Any user can search
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:NOW_SHOWING,COMING_SOON'],
            'cinema_id' => ['nullable', 'integer', 'exists:cinemas,id'],
            'genre_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
