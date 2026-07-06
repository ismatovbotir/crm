<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'name_ru', 'name_uz', 'description_ru', 'description_uz',
        'brand', 'model_number', 'unit', 'is_active', 'is_visible_portal', 'is_serial', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_visible_portal' => 'boolean',
        'is_serial' => 'boolean',
    ];

    protected static function newFactory(): \Database\Factories\ProductFactory
    {
        return \Database\Factories\ProductFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProductAttachment::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class)->orderBy('serial_number');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(BusinessTypeRecommendation::class);
    }

    public function retailPrice(): ?ProductPrice
    {
        return $this->prices->where('type', 'retail')->where('is_active', true)->first();
    }

    public function getNameAttribute(): string
    {
        return $this->name_ru ?? $this->name_uz ?? '';
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeVisiblePortal($q)
    {
        return $q->where('is_visible_portal', true)->where('is_active', true);
    }
}
