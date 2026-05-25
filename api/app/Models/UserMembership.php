<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMembership extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'remaining_sessions',
        'remaining_days',
        'total_days',
        'status',
        'last_check_in',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['status_label'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Onay Bekliyor',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_REJECTED => 'Reddedildi',
            self::STATUS_EXPIRED => 'Süresi Doldu',
            default => 'Bilinmiyor',
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) return 0;

        $endDate = $this->end_date instanceof \DateTime
            ? $this->end_date
            : \Carbon\Carbon::parse($this->end_date);

        $days = max(0, now()->diffInDays($endDate, false));
        return (int) $days;
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->total_days || $this->total_days <= 0) return 0;

        $elapsed = $this->total_days - $this->remaining_days;
        return min(100, max(0, ($elapsed / $this->total_days) * 100));
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->remaining_days > 0 && $this->remaining_days <= 7;
    }

    public function cancel()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }
}
