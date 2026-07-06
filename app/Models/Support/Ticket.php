<?php

namespace App\Models\Support;

use App\Models\Catalog\ProductSerial;
use App\Models\Customer\Contact;
use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'customer_id', 'contact_id', 'category_id', 'assignee_id', 'created_by',
        'serial_id', 'priority', 'status', 'subject', 'description', 'resolved_at', 'closed_at', 'csat_score',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'csat_score' => 'integer',
    ];

    protected static function newFactory(): \Database\Factories\TicketFactory
    {
        return \Database\Factories\TicketFactory::new();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->where('is_internal', false)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function scopeOpen($q)
    {
        return $q->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('assignee_id', $userId);
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, ['resolved', 'closed']);
    }
}
