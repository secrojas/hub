<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'          => ['required', 'exists:clients,id'],
            'concepto'           => ['required', 'string', 'max:255'],
            'fecha_emision'      => ['required', 'date'],
            'fecha_pago'         => ['nullable', 'date', 'required_if:estado,pagado'],
            'estado'             => ['required', 'in:pendiente,pagado,vencido'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.concepto'   => ['required', 'string', 'max:255'],
            'items.*.monto'      => ['required', 'numeric', 'min:0'],
            'items.*.task_id'    => ['nullable', 'exists:tasks,id'],
        ];
    }
}
