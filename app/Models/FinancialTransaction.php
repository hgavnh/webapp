<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class FinancialTransaction extends Model
{
    use HasFactory;

    public function scopeForTenant(Builder $query, $tenantId = null): Builder
    {
        return $query->where('tenant_id', $tenantId ?? auth()->user()?->tenant_id);
    }

    public function scopeForPeriod(Builder $query, $period = 'day'): Builder
    {
        $now = Carbon::now();

        return match ($period) {
            'week' => $query->whereBetween('transaction_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'month' => $query->whereMonth('transaction_date', $now->month)->whereYear('transaction_date', $now->year),
            'year' => $query->whereYear('transaction_date', $now->year),
            default => $query->whereDate('transaction_date', $now->toDateString()),
        };
    }

    protected $fillable = [
        'tenant_id',
        'type',
        'amount',
        'category',
        'note',
        'transaction_date',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
