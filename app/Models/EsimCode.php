<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsimCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'esim_type_id',
        'label',
        'status',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(EsimType::class, 'esim_type_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(EsimRequest::class, 'esim_code_id');
    }
}
