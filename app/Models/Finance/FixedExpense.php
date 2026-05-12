<?php

namespace App\Models\Finance;

use App\Enums\Finance\ExpenseCategory;
use Illuminate\Database\Eloquent\Model;

class FixedExpense extends Model
{
    protected $fillable = [
        'nombre',
        'monto',
        'dia_vencimiento',
        'categoria',
        'activo',
        'descripcion',
    ];

    protected $casts = [
        'monto'  => 'decimal:2',
        'activo' => 'boolean',
        'categoria' => ExpenseCategory::class,
    ];
}
