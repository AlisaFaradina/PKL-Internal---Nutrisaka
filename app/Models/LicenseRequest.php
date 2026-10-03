<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseRequest extends Model
{
    protected $fillable = [
        'business_name',
        'email',
        'phone',
        'address',
        'device_id',
        'app_id',
        'password',
        'status',
        'requested_at',
        'processed_at',
        'processed_by',
        'license_id',
        'license_token',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    public function processor(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'processed_by');
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
