<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['supplier_id', 'user_id', 'purchase_number', 'total', 'status', 'purchased_at'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /** Admin melihat semua transaksi; Staff hanya miliknya sendiri. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('user_id', $user->id);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /** Filter: q (nomor transaksi), status, supplier_id, date_from, date_to. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('purchase_number', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['supplier_id'] ?? null, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('purchased_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('purchased_at', '<=', $v));
    }
}
