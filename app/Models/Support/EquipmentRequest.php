<?php

namespace App\Models\Support;

use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentRequest extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'manager_id', 'subject', 'description', 'budget', 'needed_by', 'status', 'notes'];

    protected $casts = ['budget' => 'decimal:2', 'needed_by' => 'date'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function scopeByStatus($q, string $status) { return $q->where('status', $status); }
}
