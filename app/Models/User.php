<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'role',
        'password',
        'is_active',
        'suspended_until',
        'suspension_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'suspended_until' => 'datetime',
        ];
    }

    public function isSuspended(): bool
    {
        if (!$this->is_active) {
            return true;
        }

        if ($this->suspended_until && $this->suspended_until->isFuture()) {
            return true;
        }

        return false;
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
