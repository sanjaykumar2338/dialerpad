<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BatchRequest extends Model
{
    use HasFactory;

    public const PRODUCT_ESIM = 'esim';

    public const PRODUCT_CALL_CARD = 'call_card';

    public const PRODUCTS = [
        self::PRODUCT_ESIM,
        self::PRODUCT_CALL_CARD,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_SENT = 'sent';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_GENERATED,
        self::STATUS_SENT,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'account_id',
        'product_type',
        'quantity',
        'status',
        'notes',
        'generation_settings',
        'approved_by',
        'approved_at',
        'generated_at',
        'sent_at',
        'completed_at',
        'delivery_document_path',
    ];

    protected $casts = [
        'generation_settings' => 'array',
        'approved_at' => 'datetime',
        'generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function batch(): HasOne
    {
        return $this->hasOne(Batch::class, 'batch_request_id');
    }

    public function productLabel(): string
    {
        return $this->product_type === self::PRODUCT_ESIM ? 'eSIM' : 'Call card';
    }
}
