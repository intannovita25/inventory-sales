<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['user_id', 'sale_number', 'total', 'status', 'sold_at'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Sale hasMany SaleItem
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('user_id', $user->id);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /** Filter: q (nomor transaksi), status, date_from, date_to. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('sale_number', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('sold_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('sold_at', '<=', $v));
    }
}
