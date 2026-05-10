<?php

namespace App\Http\Requests;

use App\Enums\TravelEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTravelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'       => ['required', 'string', 'max:255'],
            'destino'      => ['required', 'string', 'max:255'],
            'descripcion'  => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado'       => ['required', new Enum(TravelEstado::class)],
            'notas'        => ['nullable', 'string'],
        ];
    }
}
