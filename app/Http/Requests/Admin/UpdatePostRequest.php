<?php

namespace App\Http\Requests\Admin;

use Closure;
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
            'uploaded_media' => ['nullable', 'array'],
            'uploaded_media.*' => ['string'],
        ];

        if (!empty($this->input('uploaded_media'))) {
            $rules['media'] = ['nullable'];
        } elseif ($type === 'image') {
            $rules['media'] = ['nullable', 'array', 'max:6'];
            $rules['media.*'] = ['file', 'max:20480', function (string $attribute, mixed $value, Closure $fail) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, $allowed)) {
                    $fail('Each image must be a jpg, png, webp, or heic file.');
                }
            }];
        } else {
            $rules['media'] = ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:102400'];
        }

        return $rules;
    }
}
