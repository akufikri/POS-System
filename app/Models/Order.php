<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'total_amount',
        'total_cost',
        'notes',
        'discount_type',
        'discount_value',
        'net_amount',
        'payment_status',
        'shift_id',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'total_cost' => 'integer',
        'discount_value' => 'integer',
        'net_amount' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (session('tenant_id')) {
                $query->where('tenant_id', session('tenant_id'));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnModel::class, 'order_id');
    }

    public function getProfitAttribute(): int
    {
        return $this->total_amount - $this->total_cost;
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getNetAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->net_amount ?? $this->total_amount, 0, ',', '.');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
