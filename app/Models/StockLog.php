<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StockLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'user_id',
        'quantity',
        'type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (session('tenant_id')) {
                $query->where('tenant_id', session('tenant_id'));
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
