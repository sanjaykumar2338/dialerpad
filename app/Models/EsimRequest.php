<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsimRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'esim_code_id',
        'esim_type_id',
        'full_name',
        'email',
        'phone',
        'device_model',
        'status',
        'notes',
        'provider_response',
    ];

    protected $casts = [
        'provider_response' => 'array',
    ];

    public const STATUSES = ['pending', 'processed', 'failed'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(EsimType::class, 'esim_type_id');
    }

    public function code(): BelongsTo
    {
        return $this->belongsTo(EsimCode::class, 'esim_code_id');
    }
}
