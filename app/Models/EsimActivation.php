<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsimActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'full_name',
        'country_or_device',
        'esim_type_id',
        'status',
        'provider_response_json',
        'request_uuid',
    ];

    protected $casts = [
        'provider_response_json' => 'array',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(EsimType::class, 'esim_type_id');
    }
}
