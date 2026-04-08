<?php

namespace App\Models;

use App\Enums\BillingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'concepto',
        'monto',
        'fecha_emision',
        'fecha_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado'        => BillingStatus::class,
            'fecha_emision' => 'date',
            'fecha_pago'    => 'date',
            'monto'         => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }
}
