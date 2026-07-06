<?php

namespace App\Models\Catalog;

use App\Models\BusinessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTypeRecommendation extends Model
{
    protected $fillable = [
        'business_type_id',
        'product_id',
        'priority',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeRequired($q)
    {
        return $q->where('priority', 'required');
    }

    public function scopeRecommended($q)
    {
        return $q->where('priority', 'recommended');
    }
}
