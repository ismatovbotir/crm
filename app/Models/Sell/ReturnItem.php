<?php

namespace App\Models\Sell;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'product_id',
        'name',
        'sku',
        'quantity',
        'serial_id',
        'unit_price',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function productReturn(): BelongsTo
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Product::class);
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\ProductSerial::class, 'serial_id');
    }
}
