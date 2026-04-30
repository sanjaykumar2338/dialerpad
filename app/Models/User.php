<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    public const ROLE_DISTRIBUTOR = 'distributor';

    public const ROLE_MASTER_DISTRIBUTOR = 'master_distributor';

    public const DISTRIBUTOR_ROLES = [
        self::ROLE_DISTRIBUTOR,
        self::ROLE_MASTER_DISTRIBUTOR,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'phone',
        'company_name',
        'status',
        'role',
        'password',
        'is_admin',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function isDistributor(): bool
    {
        return ! $this->is_admin && in_array($this->role ?? self::ROLE_DISTRIBUTOR, self::DISTRIBUTOR_ROLES, true);
    }

    public function batchRequests(): HasMany
    {
        return $this->hasMany(BatchRequest::class, 'account_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'account_id');
    }

    public function callCards(): HasMany
    {
        return $this->hasMany(CallCard::class, 'account_id');
    }

    public function esimCodes(): HasMany
    {
        return $this->hasMany(EsimCode::class, 'account_id');
    }
}
