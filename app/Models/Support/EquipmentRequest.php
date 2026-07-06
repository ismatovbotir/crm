<?php

namespace App\Models\Support;

use App\Models\Customer\Customer;
use App\Models\Quote\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentRequest extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'manager_id', 'subject', 'description', 'budget', 'needed_by', 'status', 'notes'];

    protected $casts = ['budget' => 'decimal:2', 'needed_by' => 'date'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EquipmentRequestComment::class)->latest();
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(EquipmentRequestComment::class)->where('is_internal', false)->latest();
    }

    public function scopeByStatus($q, string $status) { return $q->where('status', $status); }
}
