<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    protected $table = 'ticket_categories';

    protected $fillable = ['name', 'slug', 'sla_hours', 'is_active'];

    protected $casts = [
        'sla_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
