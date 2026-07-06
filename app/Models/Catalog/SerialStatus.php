<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialStatus extends Model
{
    public $timestamps = false;

    protected $fillable = ['serial_id', 'status', 'changed_by', 'notes', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
