<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'       => ['required', 'string', 'max:255'],
            'client_id'    => ['required', 'exists:clients,id'],
            'descripcion'  => ['nullable', 'string'],
            'prioridad'    => ['nullable', 'in:baja,media,alta'],
            'fecha_limite' => ['nullable', 'date'],
        ];
    }
}
