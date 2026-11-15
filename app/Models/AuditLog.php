<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'description', 'changes', 'ip'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function record(string $action, string $description, ?Model $subject = null, ?array $changes = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr($description, 0, 500),
            'changes' => $changes,
            'ip' => request()?->ip(),
        ]);
    }
}
