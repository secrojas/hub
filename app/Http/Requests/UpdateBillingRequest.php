<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'     => ['required', 'exists:clients,id'],
            'concepto'      => ['required', 'string', 'max:255'],
            'monto'         => ['required', 'numeric', 'min:0.01'],
            'fecha_emision' => ['required', 'date'],
            'fecha_pago'    => ['nullable', 'date', 'required_if:estado,pagado'],
            'estado'        => ['required', 'in:pendiente,pagado,vencido'],
        ];
    }
}
