<?php

namespace App\Http\Requests\Admin;

use Closure;
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
            'uploaded_media' => ['nullable', 'array'],
            'uploaded_media.*' => ['string'],
        ];

        // If pre-uploaded paths are provided, media file is not required
        if (!empty($this->input('uploaded_media'))) {
            $rules['media'] = ['nullable'];
        } elseif ($this->input('type') === 'image') {
            $rules['media'] = ['required', 'array', 'min:1', 'max:6'];
            $rules['media.*'] = ['file', 'max:20480', function (string $attribute, mixed $value, Closure $fail) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, $allowed)) {
                    $fail('Each image must be a jpg, png, webp, or heic file.');
                }
            }];
        } else {
            $rules['media'] = ['required', 'file', 'mimes:mp4,mov,webm', 'max:102400'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'media.*.mimes' => 'Each image must be a jpg, jpeg, png, webp, or heic file.',
            'media.*.max' => 'Each image must not exceed 20MB.',
            'media.max' => 'You can upload up to 6 images.',
        ];
    }
}
