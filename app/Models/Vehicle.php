<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vehicle extends Model
{
    protected $fillable = [
        'client_id', 'status_id', 'make', 'model', 'trim', 'year', 'color', 'vin', 'fuel_type', 'engine_size', 'transmission',
        'mileage', 'mileage_unit', 'condition', 'notes', 'purchase_location', 'auction_name', 'auction_url', 'purchase_date',
        'purchase_price', 'auction_fees', 'inland_transport', 'cost_currency', 'purchase_notes', 'departure_port', 'arrival_port',
        'shipping_company', 'vessel_name', 'container_no', 'shipping_date', 'eta', 'arrived_at', 'total_due', 'currency',
        'financial_notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'shipping_date' => 'date',
            'eta' => 'date',
            'arrived_at' => 'date',
            'archived_at' => 'datetime',
            'purchase_price' => 'decimal:3',
            'auction_fees' => 'decimal:3',
            'inland_transport' => 'decimal:3',
            'total_due' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Vehicle $v) {
            $v->file_no = 'AL-CAR-'.str_pad((string) $v->id, 6, '0', STR_PAD_LEFT);
            $v->saveQuietly();
        });
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function status(): BelongsTo { return $this->belongsTo(VehicleStatus::class, 'status_id'); }
    public function images(): HasMany { return $this->hasMany(VehicleImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'); }
    public function primaryImage(): HasOne { return $this->hasOne(VehicleImage::class)->ofMany(['is_primary' => 'max', 'id' => 'min']); }
    public function documents(): HasMany { return $this->hasMany(VehicleDocument::class); }
    public function history(): HasMany { return $this->hasMany(VehicleStatusHistory::class)->orderByDesc('created_at')->orderByDesc('id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function activePayments(): HasMany { return $this->hasMany(Payment::class)->where('status', 'active'); }
    public function costItems(): HasMany { return $this->hasMany(VehicleCostItem::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function internalNotes(): MorphMany { return $this->morphMany(InternalNote::class, 'notable')->latest(); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive(Builder $q): Builder { return $q->whereNull('archived_at'); }

    public function scopeWithPaid(Builder $q): Builder
    {
        return $q->withSum(['payments as paid_sum' => fn ($p) => $p->where('status', 'active')], 'amount');
    }

    public function title(): string
    {
        return trim($this->make.' '.$this->model.' '.($this->year ?: ''));
    }

    public function paidAmount(): string
    {
        if (array_key_exists('paid_sum', $this->attributes)) {
            return (string) ($this->attributes['paid_sum'] ?? '0');
        }

        return (string) $this->activePayments()->sum('amount');
    }

    public function financials(): array
    {
        return Money::summary((string) $this->total_due, $this->paidAmount());
    }

    /** إجمالي التكاليف الداخلية (شراء + رسوم + نقل + بنود + مصروفات مرتبطة) */
    public function totalCost(): string
    {
        $items = (string) $this->costItems()->sum('amount');
        $exp = (string) $this->expenses()->sum('amount');

        return Money::add($this->purchase_price, $this->auction_fees, $this->inland_transport, $items, $exp);
    }

    public function stageGroup(): ?string
    {
        return $this->status?->stage_group;
    }

    public function isArrived(): bool
    {
        return in_array($this->stageGroup(), ['arrived', 'delivered'], true) || $this->arrived_at !== null;
    }

    /** عدد الأيام المتبقية للوصول (سالب = متأخرة) */
    public function daysToEta(): ?int
    {
        if (! $this->eta) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->eta->copy()->startOfDay(), false);
    }

    public function isLate(): bool
    {
        return ! $this->isArrived() && $this->eta && $this->daysToEta() < 0;
    }

    public function primaryImageUrl(string $area = 'admin'): ?string
    {
        $img = $this->relationLoaded('primaryImage') ? $this->primaryImage : $this->primaryImage()->first();
        if (! $img) {
            return null;
        }

        return $area === 'portal' ? route('portal.images.show', $img) : route('admin.images.show', $img);
    }
}
