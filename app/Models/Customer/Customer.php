<?php

namespace App\Models\Customer;

use App\Models\BusinessType;
use App\Models\Lead\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'legal_name', 'inn', 'oked',
        'business_type_id', 'segment', 'status',
        'region', 'city', 'address',
        'phone', 'email', 'website',
        'bank_id', 'bank_account',
        'credit_limit', 'payment_terms_days', 'customer_since',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'customer_since' => 'date',
    ];

    protected static function newFactory(): \Database\Factories\CustomerFactory
    {
        return \Database\Factories\CustomerFactory::new();
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(\App\Models\Quote\Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Invoice\Invoice::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\Support\Ticket::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'vip']);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('users', fn ($q) => $q->where('users.id', $userId));
    }
}
