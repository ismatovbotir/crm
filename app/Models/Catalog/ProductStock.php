<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    protected $fillable = ['product_id', 'quantity', 'reserved', 'warehouse', 'notes'];

    protected $casts = [
        'quantity' => 'integer',
        'reserved' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }
}
