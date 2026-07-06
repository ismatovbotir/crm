<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin \Spatie\Permission\Traits\HasRoles
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Компании-клиенты, привязанные к данному пользователю.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Customer\Customer::class, 'customer_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Активные пользователи (для admin-фильтров).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Только сотрудники RSG (внутренний контур).
     */
    public function scopeManagers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', [
                'super-admin', 'sales-director', 'sales-manager',
                'tech-support', 'catalog-manager', 'accountant',
            ]);
        });
    }
}
