<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'      => ['required', 'string', 'max:255'],
            'contenido'   => ['nullable', 'string'],
            'folder_id'   => ['nullable', 'exists:note_folders,id'],
            'esta_fijada' => ['boolean'],
        ];
    }
}
