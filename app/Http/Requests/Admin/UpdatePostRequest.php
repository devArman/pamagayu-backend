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
        $mediaRules = ['nullable', 'file'];
        $type = $this->input('type', $this->route('post')?->type);

        if ($type === 'image') {
            $mediaRules[] = 'mimes:jpg,jpeg,png,webp';
            $mediaRules[] = 'max:10240';
        } elseif ($type === 'video') {
            $mediaRules[] = 'mimes:mp4,mov,webm';
            $mediaRules[] = 'max:102400';
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
}
