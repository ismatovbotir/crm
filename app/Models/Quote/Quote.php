<?php

namespace App\Models\Quote;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'customer_id', 'contact_id', 'manager_id', 'currency', 'exchange_rate',
        'issue_date', 'status', 'valid_until',
        'subtotal', 'discount_percent', 'discount_total', 'vat_percent', 'vat_amount', 'total',
        'version', 'notes', 'terms',
        'sent_at', 'viewed_at', 'accepted_at', 'rejected_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_percent'=> 'decimal:2',
        'discount_total'  => 'decimal:2',
        'vat_percent'     => 'decimal:2',
        'vat_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'exchange_rate'   => 'decimal:4',
        'issue_date'      => 'date',
        'valid_until'     => 'date',
        'sent_at'         => 'datetime',
        'viewed_at'       => 'datetime',
        'accepted_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'version'         => 'integer',
    ];

    protected static function newFactory(): \Database\Factories\QuoteFactory
    {
        return \Database\Factories\QuoteFactory::new();
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
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuoteVersion::class)->latest();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('manager_id', $userId);
    }

    public function scopeByStatus($q, string $status)
    {
        return $q->where('status', $status);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft']);
    }
}
