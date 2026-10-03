<?php

namespace App\Models;

use App\Traits\HasSimulationMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasSimulationMode;

    protected $fillable = [
        'payment_number',
        'sale_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'proof_file',
        'confirmation_status',
        'payment_uploaded_at',
        'confirmed_at',
        'confirmed_by',
        'rejection_reason',
        'is_simulation',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
        'payment_uploaded_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'is_simulation' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getConfirmationStatusLabelAttribute(): string
    {
        return match ($this->confirmation_status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            default => ucfirst($this->confirmation_status),
        };
    }

    public function isPending(): bool
    {
        return $this->confirmation_status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->confirmation_status === 'confirmed';
    }

    public function isRejected(): bool
    {
        return $this->confirmation_status === 'rejected';
    }

    public static function generatePaymentNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PAY-{$date}-";
        $last = static::where('payment_number', 'like', "{$prefix}%")->latest('id')->first();
        if ($last) {
            $num = (int) substr($last->payment_number, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }
}
