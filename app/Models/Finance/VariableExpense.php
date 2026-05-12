<?php

namespace App\Models\Finance;

use App\Enums\Finance\ExpenseCategory;
use Illuminate\Database\Eloquent\Model;

class VariableExpense extends Model
{
    protected $fillable = [
        'fecha',
        'monto',
        'descripcion',
        'categoria',
        'comprobante_path',
    ];

    protected $casts = [
        'fecha'     => 'date',
        'monto'     => 'decimal:2',
        'categoria' => ExpenseCategory::class,
    ];
}
