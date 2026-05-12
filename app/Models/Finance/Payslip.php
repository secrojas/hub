<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $appends = ['periodo_formateado'];

    protected $fillable = [
        'periodo',
        'empresa',
        'fecha_pago',
        'banco',
        'sueldo_basico',
        'total_bruto',
        'total_sin_aporte',
        'total_descuentos',
        'total_neto',
        'conceptos',
        'archivo_path',
    ];

    protected $casts = [
        'fecha_pago'       => 'date',
        'sueldo_basico'    => 'decimal:2',
        'total_bruto'      => 'decimal:2',
        'total_sin_aporte' => 'decimal:2',
        'total_descuentos' => 'decimal:2',
        'total_neto'       => 'decimal:2',
        'conceptos'        => 'array',
    ];

    public function getPeriodoFormateadoAttribute(): string
    {
        [$year, $month] = explode('-', $this->periodo);
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        return ($meses[(int)$month - 1] ?? $month).' '.$year;
    }
}
