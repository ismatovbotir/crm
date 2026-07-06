<?php

namespace App\Models\Quote;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteVersion extends Model
{
    protected $fillable = ['quote_id', 'version', 'items_snapshot', 'total', 'created_by'];

    protected $casts = [
        'items_snapshot' => 'array',
        'total' => 'decimal:2',
        'version' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
