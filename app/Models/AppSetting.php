<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            'supplier_name' => 'Nutrisaka Supplier Bahan Pangan & Gizi',
            'supplier_tagline' => 'Mitra Terpercaya Kebutuhan Bahan Pangan & Nutrisi SPPG',
            'supplier_phone' => '0812-3456-7890',
            'supplier_email' => 'supplier.nutrisaka@gmail.com',
            'supplier_address' => 'Jl. Pangan Sejahtera No. 88, Kawasan Logistik Pangan Nusantara',
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '137-00-9876543-2',
            'bank_account_name' => 'NUTRISAKA SUPPLIER PANGAN',
            'invoice_footer_notes' => 'Barang yang telah diterima harap diperiksa. Pembayaran transfer mohon sertakan No. Invoice.',
            'security_pin' => '1234',
            'thermal_paper_size' => '58mm',
            'application_mode' => 'real',
        ];

        $stored = static::pluck('value', 'key')->toArray();
        return array_merge($defaults, $stored);
    }

    /**
     * Mengambil mode aplikasi yang aktif (real / demo).
     */
    public static function getApplicationMode(): string
    {
        return static::get('application_mode', 'real');
    }

    /**
     * Mengatur mode aplikasi (real / demo).
     */
    public static function setApplicationMode(string $mode): void
    {
        $validMode = in_array(strtolower($mode), ['real', 'demo'], true) ? strtolower($mode) : 'real';
        static::set('application_mode', $validMode);
    }

    public static function isRealMode(): bool
    {
        return static::getApplicationMode() === 'real';
    }

    public static function isDemoMode(): bool
    {
        return static::getApplicationMode() === 'demo';
    }

    /**
     * Memverifikasi PIN keamanan (mendukung hashed bcrypt dan legacy plaintext).
     */
    public static function verifyPin(string $inputPin): bool
    {
        $stored = static::get('security_pin', '1234');
        if (empty($stored)) {
            return false;
        }

        // Jika tersimpan sebagai bcrypt hash
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$')) {
            return \Illuminate\Support\Facades\Hash::check($inputPin, $stored);
        }

        // Legacy plaintext fallback
        if ($stored === $inputPin) {
            // Otomatis migrasikan ke hash
            static::setPin($inputPin);
            return true;
        }

        return false;
    }

    /**
     * Menyimpan PIN keamanan baru dalam bentuk hash.
     */
    public static function setPin(string $newPin): void
    {
        static::set('security_pin', \Illuminate\Support\Facades\Hash::make($newPin));
    }
}

