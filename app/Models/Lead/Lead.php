<?php

namespace App\Models\Lead;

use App\Models\BusinessType;
use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'company', 'customer_id',
        'phone', 'email',
        'source_id', 'manager_id', 'created_by',
        'status', 'score', 'budget',
        'business_type_id', 'region',
        'converted_at', 'won_amount', 'lost_reason',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
        'budget' => 'decimal:2',
        'won_amount' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    protected static function newFactory(): \Database\Factories\LeadFactory
    {
        return \Database\Factories\LeadFactory::new();
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('manager_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
