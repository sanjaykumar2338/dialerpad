<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsimType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider_reference_code',
        'description',
    ];

    public function activations(): HasMany
    {
        return $this->hasMany(EsimActivation::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(EsimRequest::class);
    }
}
