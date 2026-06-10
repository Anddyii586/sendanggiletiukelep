<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';

    /**
     * Legacy statuses retained only for old records/routes.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_VERIFICATION = 'waiting_verification';

    public const STATUSES = [
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'booking_code',
        'user_id',
        'service_id',
        'visit_date',
        'participant_count',
        'contact_name',
        'contact_phone',
        'contact_email',
        'subtotal',
        'service_fee',
        'total_price',
        'status',
        'notes',
        'expires_at',
        'cancelled_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'participant_count' => 'integer',
            'subtotal' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'total_price' => 'decimal:2',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function canUploadPayment(): bool
    {
        return false;
    }

    public function canBeReviewed(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->review === null;
    }

    public function canPay(): bool
    {
        if ($this->status !== self::STATUS_WAITING_PAYMENT) {
            return false;
        }

        if ($this->expires_at?->isPast()) {
            return false;
        }

        return ! in_array($this->payment?->status, [
            Payment::STATUS_PAID,
            Payment::STATUS_CANCELLED,
            Payment::STATUS_EXPIRED,
        ], true);
    }

    public function isTicketAvailable(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_COMPLETED], true)
            && $this->payment?->status === Payment::STATUS_PAID;
    }
}
