<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_card_id',
        'session_uuid',
        'dialed_number',
        'full_number',
        'started_at',
        'ended_at',
        'duration_seconds',
        'remaining_minutes_after_call',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'remaining_minutes_after_call' => 'integer',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(CallCard::class, 'call_card_id');
    }
}
