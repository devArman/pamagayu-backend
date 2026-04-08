<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('post')?->type);

        $rules = [
            'type' => ['required', 'in:video,image'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($type === 'image') {
            $rules['media'] = ['nullable', 'array', 'max:6'];
            $rules['media.*'] = ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
        } else {
            $rules['media'] = ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:102400'];
        }

        return $rules;
    }
}
