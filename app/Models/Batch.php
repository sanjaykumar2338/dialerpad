<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    public const STATUS_GENERATED = 'generated';

    public const STATUS_SENT = 'sent';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_GENERATED,
        self::STATUS_SENT,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'batch_id',
        'account_id',
        'batch_request_id',
        'product_type',
        'status',
        'total_cards',
        'generated_by',
        'sent_at',
        'completed_at',
        'delivery_document_path',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(BatchRequest::class, 'batch_request_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function productLabel(): string
    {
        return $this->product_type === BatchRequest::PRODUCT_ESIM ? 'eSIM' : 'Call card';
    }
}
