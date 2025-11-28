<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;

class CallCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'prefix',
        'total_minutes',
        'used_minutes',
        'status',
        'notes',
        'created_by',
    ];

    protected $appends = [
        'remaining_minutes',
        'qr_code_path',
    ];

    protected $casts = [
        'used_minutes' => 'integer',
        'total_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CallCard $card): void {
            if ($card->used_minutes === null) {
                $card->used_minutes = 0;
            }
            // Keep usage within the allowed total and derive status automatically.
            $card->used_minutes = min($card->used_minutes, $card->total_minutes);
            $card->status = $card->used_minutes >= $card->total_minutes ? 'expired' : 'active';
        });
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRemainingMinutesAttribute(): int
    {
        return max(0, $this->total_minutes - $this->used_minutes);
    }

    public function markExpiredIfNeeded(): void
    {
        $this->status = $this->used_minutes >= $this->total_minutes ? 'expired' : 'active';
        $this->save();
    }

    public function getQrCodePathAttribute(): string
    {
        return 'storage/qrcodes/' . $this->uuid . '.png';
    }
}
