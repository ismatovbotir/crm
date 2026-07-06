<?php

namespace App\Models\Sell;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductSerial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sell_id', 'product_id', 'quantity', 'unit_price',
        'discount_percent', 'total', 'sort_order', 'notes',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    protected static function newFactory(): \Database\Factories\SellItemFactory
    {
        return \Database\Factories\SellItemFactory::new();
    }

    public function sell(): BelongsTo
    {
        return $this->belongsTo(Sell::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'sell_item_id');
    }
}
