<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'in:video,image'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($this->input('type') === 'image') {
            $rules['media'] = ['required', 'array', 'min:1', 'max:6'];
            $rules['media.*'] = ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
        } else {
            $rules['media'] = ['required', 'file', 'mimes:mp4,mov,webm', 'max:102400'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'media.*.mimes' => 'Each image must be a jpg, jpeg, png, or webp file.',
            'media.*.max' => 'Each image must not exceed 10MB.',
            'media.max' => 'You can upload up to 6 images.',
        ];
    }
}
