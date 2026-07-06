<?php

namespace App\Models\Invoice;

use App\Models\Customer\Customer;
use App\Models\Quote\Quote;
use App\Models\Sell\Sell;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'number', 'quote_id', 'customer_id', 'manager_id', 'currency', 'exchange_rate',
        'status', 'shipment_status', 'due_date', 'subtotal', 'tax_rate', 'tax_amount', 'total',
        'paid_amount', 'notes', 'sent_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'due_date' => 'date',
        'sent_at' => 'datetime',
    ];

    protected static function newFactory(): \Database\Factories\InvoiceFactory
    {
        return \Database\Factories\InvoiceFactory::new();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'number', 'quote_id', 'customer_id', 'manager_id', 'status',
                'shipment_status', 'due_date', 'total', 'paid_amount', 'sent_at',
            ])
            ->logOnlyDirty()
            ->useLogName('invoice');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at');
    }

    public function sells(): HasMany
    {
        return $this->hasMany(Sell::class)->orderBy('sold_at');
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(\App\Models\Sell\ProductReturn::class, 'invoice_id');
    }

    public function getRemainingAttribute(): string
    {
        return bcsub((string) $this->total, (string) $this->paid_amount, 2);
    }

    public function scopeOverdue($q)
    {
        return $q->where('due_date', '<', now())->whereNotIn('status', ['paid', 'cancelled']);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('manager_id', $userId);
    }
}
