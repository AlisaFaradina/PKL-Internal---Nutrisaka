<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class License extends Model
{
    protected $fillable = [
        'token_masked',
        'license_payload',
        'signature',
        'public_key',
        'device_fingerprint',
        'status',
        'activated_at',
        'last_validated_at',
        'grace_until',
        'is_trial',
        'trial_started_at',
        'trial_ends_at',
        'first_run_at',
        'last_seen_at',
        'trial_signature',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'grace_until' => 'datetime',
        'is_trial' => 'boolean',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'first_run_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Decode payload JSON menjadi array.
     */
    public function getPayloadAttribute(): array
    {
        if (empty($this->license_payload)) {
            return [];
        }

        $decoded = json_decode($this->license_payload, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Memeriksa apakah lisensi aktif dan dapat digunakan.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'grace'], true);
    }

    /**
     * Memeriksa apakah lisensi sedang dalam masa tenggang offline (grace period).
     * Lisensi Nutrisaka berlaku selamanya (lifetime), tidak ada grace period expiry.
     */
    public function isInGrace(): bool
    {
        return false;
    }

    /**
     * Memeriksa apakah lisensi terkunci (wajib cek ulang ke server).
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Menghitung sisa hari sebelum aplikasi terkunci.
     * Return null karena lisensi permanen seumur hidup perangkat.
     */
    public function daysUntilLock(): ?int
    {
        return null;
    }

    /**
     * Memeriksa apakah sedang dalam masa trial.
     */
    public function isTrial(): bool
    {
        return $this->is_trial === true;
    }

    /**
     * Memeriksa apakah trial masih aktif.
     */
    public function isTrialActive(): bool
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return false;
        }
        return now()->lt($this->trial_ends_at);
    }

    /**
     * Memeriksa apakah trial telah berakhir.
     */
    public function isTrialExpired(): bool
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return false;
        }
        return now()->gte($this->trial_ends_at);
    }

    /**
     * Menghitung sisa hari trial.
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return null;
        }
        $days = (int) ceil(now()->diffInSeconds($this->trial_ends_at, false) / 86400);
        return max(0, $days);
    }

    /**
     * Menghitung sisa waktu masa trial dalam hari dan jam nyata.
     */
    public function trialTimeRemaining(): array
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return ['days' => 0, 'hours' => 0, 'formatted' => '0 hari'];
        }

        if ($this->isTrialExpired()) {
            return ['days' => 0, 'hours' => 0, 'formatted' => 'Telah Berakhir'];
        }

        $now = now();
        $diff = $now->diff($this->trial_ends_at);
        $days = (int) $diff->days;
        $hours = (int) $diff->h;

        return [
            'days' => $days,
            'hours' => $hours,
            'formatted' => "{$days} hari {$hours} jam",
        ];
    }

    /**
     * Memulai trial 7 hari (tidak mereset jika sudah dimulai).
     */
    public function startTrial(): void
    {
        if ($this->trial_started_at !== null) {
            return;
        }

        $now = now();
        $this->update([
            'is_trial' => true,
            'trial_started_at' => $now,
            'trial_ends_at' => $now->copy()->addDays(7),
            'first_run_at' => $this->first_run_at ?? $now,
            'status' => 'active',
        ]);
    }

    /**
     * Mendapatkan status lengkap trial untuk UI.
     */
    public function getTrialStatusAttribute(): string
    {
        if (!$this->is_trial) {
            return 'not_trial';
        }

        if ($this->isTrialActive()) {
            return 'active';
        }

        if ($this->isTrialExpired()) {
            return 'expired';
        }

        return 'unknown';
    }

    /**
     * Mendapatkan label status trial untuk UI.
     */
    public function getTrialStatusLabelAttribute(): string
    {
        return match ($this->trial_status) {
            'not_trial' => 'Licensed',
            'active' => 'Trial Active',
            'expired' => 'Trial Expired',
            default => 'Unknown',
        };
    }
}
