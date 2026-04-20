<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'status',
        'midtrans_transaction_id',
        'midtrans_response',
        'paid_at',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDigital($query)
    {
        return $query->whereIn('method', ['qris', 'gopay', 'ovo', 'dana']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDigital(): bool
    {
        return in_array($this->method, ['qris', 'gopay', 'ovo', 'dana']);
    }
}
