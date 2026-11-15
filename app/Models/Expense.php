<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const CATEGORIES = ['إيجار', 'رواتب', 'شحن', 'تخليص جمركي', 'نقل', 'رسوم حكومية', 'تسويق', 'مصروفات تشغيلية', 'أخرى'];

    protected $fillable = ['vehicle_id', 'category', 'description', 'amount', 'expense_date', 'reference', 'created_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:3', 'expense_date' => 'date'];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
