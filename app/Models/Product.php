<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'description',
        'price',
        'cost',
        'image_url',
        'is_active',
        'stock',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'cost' => 'integer',
        'stock' => 'integer',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCostFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->cost, 0, ',', '.');
    }
}
