<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = [
        'cash' => 'نقدًا',
        'bank_transfer' => 'تحويل بنكي',
        'card' => 'بطاقة بنكية',
        'other' => 'أخرى',
    ];

    protected $fillable = ['receipt_no', 'client_id', 'vehicle_id', 'amount', 'currency', 'paid_at', 'method', 'reference', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:3', 'paid_at' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function canceller(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }

    public function scopeActive(Builder $q): Builder { return $q->where('status', 'active'); }

    public function methodLabel(): string { return self::METHODS[$this->method] ?? $this->method; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    /** المدفوع على نفس السيارة قبل هذه الدفعة (للإيصال) */
    public function paidBefore(): string
    {
        return (string) static::active()->where('vehicle_id', $this->vehicle_id)->where('id', '<>', $this->id)
            ->where(fn ($q) => $q->where('paid_at', '<', $this->paid_at)
                ->orWhere(fn ($q2) => $q2->where('paid_at', $this->paid_at)->where('id', '<', $this->id)))
            ->sum('amount');
    }
}
