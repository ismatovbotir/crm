<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialOwner extends Model
{
    protected $fillable = [
        'serial_id',
        'customer_id',
        'sell_item_id',
        'return_item_id',
        'acquired_at',
        'released_at',
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class);
    }
}
