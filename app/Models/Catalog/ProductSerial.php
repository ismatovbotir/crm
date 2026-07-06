<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSerial extends Model
{
    protected $fillable = [
        'product_id',
        'serial_number',
        'is_external',
        'ext_brand',
        'ext_model',
        'current_status',
        'current_owner_id',
        'notes',
    ];

    protected $casts = [
        'is_external'    => 'boolean',
        'current_status' => 'string',
    ];

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_external) {
            return trim(($this->ext_brand ?? '') . ' ' . ($this->ext_model ?? '')) ?: 'Внешнее оборудование';
        }
        return $this->product?->name_ru ?? 'Товар #' . $this->product_id;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currentOwner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'current_owner_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\Support\Ticket::class, 'serial_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(SerialStatus::class, 'serial_id')->latest('created_at');
    }

    public function ownerHistory(): HasMany
    {
        return $this->hasMany(SerialOwner::class, 'serial_id')->latest();
    }

    public function scopeExternal($q) { return $q->where('is_external', true); }
    public function scopeInternal($q) { return $q->where('is_external', false); }

    public function scopeAvailable($q)
    {
        return $q->where('current_status', 'available');
    }

    public function scopeSold($q)
    {
        return $q->where('current_status', 'sold');
    }

    public function scopeReturned($q)
    {
        return $q->where('current_status', 'returned');
    }
}
