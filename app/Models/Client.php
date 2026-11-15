<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Crypt;

class Client extends Model
{
    protected $fillable = ['full_name', 'phone', 'phone_alt', 'email', 'governorate', 'wilayat', 'address', 'internal_notes', 'is_active'];

    protected $hidden = ['civil_id', 'civil_id_hash'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'archived_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::created(function (Client $client) {
            $client->client_no = 'AL-'.str_pad((string) $client->id, 6, '0', STR_PAD_LEFT);
            $client->saveQuietly();
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function vehicles(): HasMany { return $this->hasMany(Vehicle::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function documents(): HasMany { return $this->hasMany(VehicleDocument::class); }
    public function internalNotes(): MorphMany { return $this->morphMany(InternalNote::class, 'notable')->latest(); }

    public function scopeActive(Builder $q): Builder { return $q->whereNull('archived_at'); }

    /** تخزين الرقم المدني مشفرًا مع بصمة للبحث وآخر 4 أرقام للعرض */
    public function setCivilId(?string $civilId): void
    {
        $civilId = $civilId !== null ? preg_replace('/\s+/', '', $civilId) : null;
        if (! $civilId) {
            $this->civil_id = null;
            $this->civil_id_hash = null;
            $this->civil_id_last4 = null;

            return;
        }
        $this->civil_id = Crypt::encryptString($civilId);
        $this->civil_id_hash = self::civilHash($civilId);
        $this->civil_id_last4 = mb_substr($civilId, -4);
    }

    public static function civilHash(string $civilId): string
    {
        return hash_hmac('sha256', preg_replace('/\s+/', '', $civilId), config('app.key'));
    }

    public function civilIdPlain(): ?string
    {
        if (! $this->civil_id) {
            return null;
        }
        try {
            return Crypt::decryptString($this->civil_id);
        } catch (\Throwable) {
            return null;
        }
    }

    public function maskedCivilId(): string
    {
        return $this->civil_id_last4 ? '•••••'.$this->civil_id_last4 : '—';
    }

    /** الملخص المالي للعميل محسوبًا من قاعدة البيانات */
    public function financials(): array
    {
        $due = (string) $this->vehicles()->sum('total_due');
        $paid = (string) Payment::where('client_id', $this->id)->where('status', 'active')->sum('amount');

        return Money::summary($due, $paid);
    }

    /** إضافة أعمدة المستحق والمدفوع لاستعلام العملاء */
    public function scopeWithFinancials(Builder $q): Builder
    {
        return $q->addSelect([
            'due_sum' => Vehicle::selectRaw('COALESCE(SUM(total_due),0)')->whereColumn('vehicles.client_id', 'clients.id'),
            'paid_sum' => Payment::selectRaw('COALESCE(SUM(amount),0)')->whereColumn('payments.client_id', 'clients.id')->where('status', 'active'),
        ])->withCount('vehicles');
    }
}
