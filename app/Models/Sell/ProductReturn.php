<?php

namespace App\Models\Sell;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'invoice_id',
        'sell_id',
        'customer_id',
        'manager_id',
        'ticket_id',
        'reason',
        'status',
        'refund_amount',
        'currency',
        'notes',
        'refunded_at',
    ];

    protected $casts = [
        'refunded_at'   => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function sell(): BelongsTo
    {
        return $this->belongsTo(Sell::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice\Invoice::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Support\Ticket::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
