<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:note_folders,id'],
            'color'     => ['nullable', 'string', 'max:20'],
        ];
    }
}
