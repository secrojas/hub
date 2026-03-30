<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'           => ['required', 'exists:clients,id'],
            'titulo'              => ['required', 'string', 'max:255'],
            'notas'               => ['nullable', 'string'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.descripcion' => ['required', 'string', 'max:500'],
            'items.*.precio'      => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
