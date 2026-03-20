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
        $mediaRules = ['required', 'file'];

        if ($this->input('type') === 'image') {
            $mediaRules[] = 'mimes:jpg,jpeg,png,webp';
            $mediaRules[] = 'max:10240'; // 10MB
        } elseif ($this->input('type') === 'video') {
            $mediaRules[] = 'mimes:mp4,mov,webm';
            $mediaRules[] = 'max:102400'; // 100MB
        }

        return [
            'type' => ['required', 'in:video,image'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media' => $mediaRules,
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.mimes' => 'The media file must match the selected type (images: jpg, png, webp; videos: mp4, mov, webm).',
        ];
    }
}
