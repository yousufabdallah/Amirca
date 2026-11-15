<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    public const TYPES = [
        'purchase_invoice' => 'فاتورة الشراء',
        'shipping' => 'مستند شحن',
        'customs' => 'مستند تخليص جمركي',
        'client' => 'مستند خاص بالعميل',
        'other' => 'أخرى',
    ];

    protected $fillable = ['client_id', 'vehicle_id', 'type', 'title', 'path', 'original_name', 'size', 'visible_to_client', 'uploaded_by'];

    protected function casts(): array
    {
        return ['visible_to_client' => 'boolean'];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function typeLabel(): string { return self::TYPES[$this->type] ?? $this->type; }
}
