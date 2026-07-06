<?php

namespace App\Models\Sell;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sell extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'invoice_id', 'customer_id', 'manager_id',
        'status', 'sold_at', 'currency', 'exchange_rate',
        'subtotal', 'total', 'notes',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'total'         => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'sold_at'       => 'date',
    ];

    protected static function newFactory(): \Database\Factories\SellFactory
    {
        return \Database\Factories\SellFactory::new();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SellItem::class)->orderBy('sort_order');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('manager_id', $userId);
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(ProductReturn::class, 'sell_id');
    }

    public function scopeByStatus($q, string $status)
    {
        return $q->where('status', $status);
    }
}
