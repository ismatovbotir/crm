<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mfo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function newFactory(): \Database\Factories\BankFactory
    {
        return \Database\Factories\BankFactory::new();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
