<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankAccount extends Model
{
    protected $fillable = ['nombre', 'tipo', 'color', 'orden', 'activo'];

    protected $appends = ['tipo_label'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function balances(): HasMany
    {
        return $this->hasMany(BankAccountBalance::class)->orderByDesc('fecha');
    }

    public function latestBalance(): HasOne
    {
        return $this->hasOne(BankAccountBalance::class)->latestOfMany('fecha');
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'caja_ahorro'       => 'Caja de Ahorro',
            'cuenta_corriente'  => 'Cuenta Corriente',
            'billetera_digital' => 'Billetera Digital',
            default             => $this->tipo,
        };
    }
}
